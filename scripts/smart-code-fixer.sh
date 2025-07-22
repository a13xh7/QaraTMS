#!/bin/bash

# Smart Code Fixer - Suggests and creates patches for common issues
# This script analyzes code and provides specific fixes with patches

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

echo -e "${BLUE}🔧 Starting Smart Code Fixer...${NC}"

# Check if we're in a merge request
if [ "$CI_PIPELINE_SOURCE" != "merge_request_event" ]; then
    echo "Not a merge request, skipping smart fixer"
    exit 0
fi

# Check if required environment variables are set
if [ -z "$GITLAB_TOKEN" ]; then
    echo -e "${RED}❌ GITLAB_TOKEN is not set. Cannot post fix suggestions.${NC}"
    echo -e "${YELLOW}Please set GITLAB_TOKEN in GitLab CI/CD Settings > Variables${NC}"
    exit 1
fi

# Check for required tools and provide alternatives
echo -e "${YELLOW}🔧 Checking available tools...${NC}"
CURL_AVAILABLE=false
JQ_AVAILABLE=false
GIT_AVAILABLE=false
GREP_AVAILABLE=false
SED_AVAILABLE=false
AWK_AVAILABLE=false
PATCH_AVAILABLE=false

if command -v curl >/dev/null 2>&1; then
    CURL_AVAILABLE=true
    echo -e "${GREEN}✅ curl is available${NC}"
else
    echo -e "${YELLOW}⚠️ curl not available - will use alternative methods${NC}"
fi

if command -v jq >/dev/null 2>&1; then
    JQ_AVAILABLE=true
    echo -e "${GREEN}✅ jq is available${NC}"
else
    echo -e "${YELLOW}⚠️ jq not available - will use basic text processing${NC}"
fi

if command -v git >/dev/null 2>&1; then
    GIT_AVAILABLE=true
    echo -e "${GREEN}✅ git is available${NC}"
else
    echo -e "${YELLOW}⚠️ git not available${NC}"
fi

if command -v grep >/dev/null 2>&1; then
    GREP_AVAILABLE=true
    echo -e "${GREEN}✅ grep is available${NC}"
else
    echo -e "${RED}❌ grep not available - cannot proceed${NC}"
    exit 1
fi

if command -v sed >/dev/null 2>&1; then
    SED_AVAILABLE=true
    echo -e "${GREEN}✅ sed is available${NC}"
else
    echo -e "${YELLOW}⚠️ sed not available - will use basic text processing${NC}"
fi

if command -v awk >/dev/null 2>&1; then
    AWK_AVAILABLE=true
    echo -e "${GREEN}✅ awk is available${NC}"
else
    echo -e "${YELLOW}⚠️ awk not available - will use basic text processing${NC}"
fi

if command -v patch >/dev/null 2>&1; then
    PATCH_AVAILABLE=true
    echo -e "${GREEN}✅ patch is available${NC}"
else
    echo -e "${YELLOW}⚠️ patch not available - manual fixes will be suggested${NC}"
fi

# Initialize variables
FIXES_COMMENT=""
PATCHES=""
FIXES_APPLIED=0
SUGGESTIONS_MADE=0

# Function to add section to fixes comment
add_section() {
    local title="$1"
    local content="$2"
    FIXES_COMMENT="${FIXES_COMMENT}\n\n${content}"
}

# Function to create a patch for a specific issue
create_patch() {
    local file="$1"
    local line_number="$2"
    local issue="$3"
    local before_code="$4"
    local after_code="$5"
    local priority="$6"
    
    # Create patch content
    local patch_content="--- a/${file}\n+++ b/${file}\n@@ -${line_number},1 +${line_number},1 @@\n-${before_code}\n+${after_code}"
    
    if [ "$PATCH_AVAILABLE" = true ]; then
        PATCHES="${PATCHES}\n\n### ${priority} ${file}:${line_number}\n**Issue:** ${issue}\n\n**Patch:**\n\`\`\`diff\n${patch_content}\n\`\`\`\n\n**Apply this fix:**\n\`\`\`bash\necho '${patch_content}' | patch -p1\n\`\`\`"
    else
        PATCHES="${PATCHES}\n\n### ${priority} ${file}:${line_number}\n**Issue:** ${issue}\n\n**Manual Fix Required:**\n\`\`\`diff\n${patch_content}\n\`\`\`\n\n**Apply manually by editing the file.**"
    fi
    
    SUGGESTIONS_MADE=$((SUGGESTIONS_MADE + 1))
}

# Function to suggest environment variable validation fix
suggest_env_validation_fix() {
    local file="$1"
    local line_number="$2"
    local line="$3"
    
    # Extract variable name
    local var_name=""
    if [ "$SED_AVAILABLE" = true ]; then
        var_name=$(echo "$line" | sed "s/.*env('\([^']*\)').*/\1/")
    else
        # Basic fallback without sed
        var_name=$(echo "$line" | grep -o "env('[^']*')" | cut -d"'" -f2)
    fi
    
    # Create before and after code
    local before_code="$line"
    local after_code="if (empty(env('${var_name}'))) {\n    throw new \\InvalidArgumentException('Required environment variable ${var_name} is not set or empty');\n}\n${line}"
    
    create_patch "$file" "$line_number" "Environment variable used without validation" "$before_code" "$after_code" "🔧"
}

# Function to suggest magic number constant fix
suggest_magic_number_fix() {
    local file="$1"
    local line_number="$2"
    local line="$3"
    
    # Find the class definition line
    local class_line=""
    if [ "$GREP_AVAILABLE" = true ]; then
        class_line=$(grep -n "class.*{" "$file" | head -1 | cut -d: -f1)
    fi
    
    if [ -n "$class_line" ]; then
        local before_code="$line"
        local after_code="private const START_YEAR = 2023;\nprivate const MONTHS = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];\n\n// Replace the original line with:\n\$listOfYears = range(self::START_YEAR, Carbon::now()->year);\n\$listOfMonth = self::MONTHS;"
        
        create_patch "$file" "$class_line" "Magic numbers should be constants" "$before_code" "$after_code" "🔧"
    fi
}

# Function to suggest PHPDoc comment fix
suggest_phpdoc_fix() {
    local file="$1"
    local line_number="$2"
    local line="$3"
    
    # Extract function name
    local func_name=""
    if [ "$SED_AVAILABLE" = true ]; then
        func_name=$(echo "$line" | sed 's/.*function \([a-zA-Z_][a-zA-Z0-9_]*\).*/\1/')
    else
        # Basic fallback without sed
        func_name=$(echo "$line" | grep -o 'function [a-zA-Z_][a-zA-Z0-9_]*' | cut -d' ' -f2)
    fi
    
    # Generate PHPDoc comment
    local phpdoc_comment="/**\n * ${func_name} description\n *\n * @param mixed \$param Description\n * @return mixed Description\n */"
    
    create_patch "$file" "$line_number" "Missing PHPDoc comment for public method" "$line" "${phpdoc_comment}\n${line}" "📝"
}

# Function to suggest exception handling fix
suggest_exception_fix() {
    local file="$1"
    local line_number="$2"
    local line="$3"
    
    local before_code="$line"
    local after_code="try {\n    // Your code here\n} catch (\\GuzzleHttp\\Exception\\RequestException \$e) {\n    Log::error('API request failed', [\n        'message' => \$e->getMessage(),\n        'file' => \$e->getFile(),\n        'line' => \$e->getLine()\n    ]);\n    // Handle API errors\n} catch (\\InvalidArgumentException \$e) {\n    Log::warning('Invalid argument provided', [\n        'message' => \$e->getMessage()\n    ]);\n    // Handle validation errors\n} catch (\\Exception \$e) {\n    Log::error('Unexpected error occurred', [\n        'message' => \$e->getMessage(),\n        'file' => \$e->getFile(),\n        'line' => \$e->getLine(),\n        'trace' => \$e->getTraceAsString()\n    ]);\n    // Handle other unexpected errors\n}"
    
    create_patch "$file" "$line_number" "Generic exception handling should be more specific" "$before_code" "$after_code" "🛡️"
}

# Function to suggest Laravel model usage fix
suggest_model_usage_fix() {
    local file="$1"
    
    # Check if file exists and is readable
    if [ ! -f "$file" ] || [ ! -r "$file" ]; then
        return
    fi
    
    # Find DB::table usage
    local db_lines=""
    if [ "$GREP_AVAILABLE" = true ]; then
        db_lines=$(grep -n "DB::table" "$file" || true)
    fi
    
    if [ -n "$db_lines" ]; then
        echo "$db_lines" | while read -r line_info; do
            local line_number=$(echo "$line_info" | cut -d: -f1)
            local line_content=$(echo "$line_info" | cut -d: -f2-)
            
            # Extract table name
            local table_name=""
            if [ "$SED_AVAILABLE" = true ]; then
                table_name=$(echo "$line_content" | sed "s/.*DB::table('\([^']*\)').*/\1/")
            else
                table_name=$(echo "$line_content" | grep -o "DB::table('[^']*')" | cut -d"'" -f2)
            fi
            
            # Suggest model usage
            local before_code="$line_content"
            local after_code="// Replace with Eloquent model\n// First, create a model: php artisan make:model ${table_name^}\n// Then use:\n${table_name^}::where('id', \$id)->get();"
            
            create_patch "$file" "$line_number" "Consider using Eloquent models instead of raw DB queries" "$before_code" "$after_code" "🎯"
        done
    fi
}

# Function to suggest validation fix
suggest_validation_fix() {
    local file="$1"
    
    # Check if file exists and is readable
    if [ ! -f "$file" ] || [ ! -r "$file" ]; then
        return
    fi
    
    # Find request()->all() usage
    local request_lines=""
    if [ "$GREP_AVAILABLE" = true ]; then
        request_lines=$(grep -n "request()->all()\|Request::all()" "$file" || true)
    fi
    
    if [ -n "$request_lines" ]; then
        echo "$request_lines" | while read -r line_info; do
            local line_number=$(echo "$line_info" | cut -d: -f1)
            local line_content=$(echo "$line_info" | cut -d: -f2-)
            
            local before_code="$line_content"
            local after_code="// Replace with proper validation\n\$data = \$request->validate([\n    'name' => 'required|string|max:255',\n    'email' => 'required|email|unique:users',\n    'password' => 'required|min:8|confirmed'\n]);"
            
            create_patch "$file" "$line_number" "Add proper request validation" "$before_code" "$after_code" "✅"
        done
    fi
}

# Function to analyze and suggest fixes for a PHP file
analyze_and_suggest_fixes() {
    local file="$1"
    local line_number=0
    
    # Check if file exists and is readable
    if [ ! -f "$file" ] || [ ! -r "$file" ]; then
        echo -e "${YELLOW}⚠️ Cannot read file: $file${NC}"
        return
    fi
    
    echo -e "${PURPLE}Analyzing and suggesting fixes for: $file${NC}"
    
    while IFS= read -r line; do
        line_number=$((line_number + 1))
        
        # Check for environment variable usage without validation
        if echo "$line" | grep -q "env('[^']*')" && ! echo "$line" | grep -q "empty\|isset\|throw"; then
            suggest_env_validation_fix "$file" "$line_number" "$line"
        fi
        
        # Check for magic numbers
        if echo "$line" | grep -q "range(2023\|[1,2,3,4,5,6,7,8,9,10,11,12]" && ! echo "$line" | grep -q "const\|private const"; then
            suggest_magic_number_fix "$file" "$line_number" "$line"
        fi
        
        # Check for missing PHPDoc comments
        if echo "$line" | grep -q "public function\|protected function" && ! echo "$line" | grep -q "/\*\*\|@param\|@return"; then
            suggest_phpdoc_fix "$file" "$line_number" "$line"
        fi
        
        # Check for generic exception handling
        if echo "$line" | grep -q "catch (\\\\Exception" && ! echo "$line" | grep -q "catch (\\\\GuzzleHttp\\\\Exception\|catch (\\\\InvalidArgumentException"; then
            suggest_exception_fix "$file" "$line_number" "$line"
        fi
        
    done < "$file"
    
    # Analyze Laravel-specific patterns
    suggest_model_usage_fix "$file"
    suggest_validation_fix "$file"
}

# Function to generate comprehensive fixes summary
generate_fixes_summary() {
    local summary=""
    
    if [ "$SUGGESTIONS_MADE" -gt 0 ]; then
        summary="${summary}🔧 **${SUGGESTIONS_MADE} specific fixes** suggested\n"
        if [ "$PATCH_AVAILABLE" = true ]; then
            summary="${summary}💡 **Ready-to-apply patches** provided\n"
        else
            summary="${summary}💡 **Manual fixes** required\n"
        fi
        summary="${summary}📋 **Step-by-step instructions** included\n"
    else
        summary="✅ **No fixes needed** - Code looks good! 🎉"
    fi
    
    add_section "🔧 Smart Fixes Summary" "${summary}"
}

# Function to generate quick fix commands
generate_quick_fix_commands() {
    if [ "$SUGGESTIONS_MADE" -gt 0 ]; then
        local commands="### Quick Fix Commands\n\n"
        commands="${commands}\`\`\`bash\n# Apply all suggested fixes automatically\n"
        commands="${commands}# Note: Review each patch before applying\n\n"
        commands="${commands}# 1. Create backup\ncp -r app/ app_backup/\n\n"
        
        if [ "$PATCH_AVAILABLE" = true ]; then
            commands="${commands}# 2. Apply patches (run these one by one)\n"
            commands="${commands}# Each patch will be shown below\n\`\`\`"
        else
            commands="${commands}# 2. Apply fixes manually\n"
            commands="${commands}# Each fix will be shown below with manual instructions\n\`\`\`"
        fi
        
        add_section "⚡ Quick Fix Commands" "${commands}"
    fi
}

# Main analysis and fix suggestion process
echo -e "${YELLOW}🔍 Analyzing PHP files and suggesting fixes...${NC}"

# Check if app directory exists
if [ ! -d "app" ]; then
    echo -e "${YELLOW}⚠️ app directory not found, checking current directory for PHP files...${NC}"
    PHP_FILES=$(find . -name "*.php" -type f 2>/dev/null || echo "")
else
    PHP_FILES=$(find app/ -name "*.php" -type f 2>/dev/null || echo "")
fi

if [ -z "$PHP_FILES" ]; then
    echo -e "${YELLOW}⚠️ No PHP files found to analyze${NC}"
    add_section "🔧 Smart Fixes Summary" "✅ **No PHP files found to analyze**\n\nThis fixer found no PHP files in the expected locations. If this is unexpected, please check the file structure."
else
    echo "$PHP_FILES" | while read -r file; do
        if [ -n "$file" ]; then
            analyze_and_suggest_fixes "$file"
        fi
    done
fi

# Generate summary sections
FIXES_COMMENT="# 🔧 Smart Code Fixes\n"

if [ "$SUGGESTIONS_MADE" -gt 0 ]; then
    FIXES_COMMENT+="\n🔧 **${SUGGESTIONS_MADE} specific fixes** suggested\n"
    if [ "$PATCH_AVAILABLE" = true ]; then
        FIXES_COMMENT+="\n💡 **Ready-to-apply patches** provided\n"
    else
        FIXES_COMMENT+="\n💡 **Manual fixes** required\n"
    fi
    FIXES_COMMENT+="\n📋 **Step-by-step instructions** included\n"
else
    FIXES_COMMENT+="\n✅ **No fixes needed** - Code looks good! 🎉\n"
fi

FIXES_COMMENT+="\n---\n\n## 📚 Laravel Best Practices Guide\n\n### Code Quality Checklist\n- [ ] Environment variables are validated\n- [ ] Magic numbers are replaced with constants\n- [ ] All public methods have PHPDoc comments\n- [ ] Exception handling is specific and logged\n- [ ] Eloquent models are used instead of raw DB queries\n- [ ] Request validation is implemented\n- [ ] Proper error logging is in place\n\n### Security Checklist\n- [ ] User inputs are validated and sanitized\n- [ ] SQL injection is prevented\n- [ ] XSS protection is implemented\n- [ ] CSRF protection is enabled\n- [ ] Authentication is properly implemented\n- [ ] Authorization is checked for all actions\n\n### Performance Checklist\n- [ ] N+1 queries are avoided\n- [ ] Database queries are optimized\n- [ ] Caching is implemented where appropriate\n- [ ] Large datasets are processed in chunks\n- [ ] Unnecessary database calls are eliminated\n\n---\n\n## ℹ️ About Smart Code Fixer\n\nThis smart code fixer automatically analyzes your code and provides:\n\n**🔧 Specific Fixes:**\n- Ready-to-apply patches\n- Step-by-step instructions\n- Before/after code examples\n\n**💡 Intelligent Suggestions:**\n- Laravel best practices\n- Security improvements\n- Performance optimizations\n\n**📋 Priority Categories:**\n- 🚨 Critical: Security vulnerabilities\n- ⚠️ Warning: Code quality issues\n- 💡 Improvement: Best practice suggestions\n- 🔧 Refactor: Code structure improvements\n\n**Environment Info:**\n- Shell Executor: \`${SHELL:-unknown}\`\n- Available Tools: curl=${CURL_AVAILABLE}, patch=${PATCH_AVAILABLE}, git=${GIT_AVAILABLE}\n- PHP Files Analyzed: $(echo "$PHP_FILES" | wc -l)\n\n> **Apply fixes carefully and test thoroughly!**\n\n---\n"

# Post the fixes comment
echo -e "${GREEN}📝 Posting smart fixes comment...${NC}"

# Escape the comment for JSON
if [ "$SED_AVAILABLE" = true ]; then
    ESCAPED_COMMENT=$(echo -e "$FIXES_COMMENT" | sed 's/"/\\"/g' | sed ':a;N;$!ba;s/\n/\\n/g')
else
    # Basic fallback without sed
    ESCAPED_COMMENT=$(echo -e "$FIXES_COMMENT" | tr '"' '\\"' | tr '\n' ' ')
fi

# Post to GitLab MR
if [ "$CURL_AVAILABLE" = true ]; then
    curl --request POST \
      --header "PRIVATE-TOKEN: $GITLAB_TOKEN" \
      --header "Content-Type: application/json" \
      --data "{\"body\":\"$ESCAPED_COMMENT\"}" \
      "https://gitlab.com/api/v4/projects/$CI_PROJECT_ID/merge_requests/$CI_MERGE_REQUEST_IID/notes" || {
        echo -e "${RED}❌ Failed to post smart fixes comment${NC}"
        echo -e "${YELLOW}Debug info:${NC}"
        echo "GITLAB_TOKEN: ${GITLAB_TOKEN:0:10}..."
        echo "CI_PROJECT_ID: $CI_PROJECT_ID"
        echo "CI_MERGE_REQUEST_IID: $CI_MERGE_REQUEST_IID"
        echo -e "${YELLOW}Comment preview:${NC}"
        echo -e "$FIXES_COMMENT"
        exit 1
    }
else
    echo -e "${RED}❌ curl not available, cannot post fix suggestions${NC}"
    echo -e "${YELLOW}Comment that would have been posted:${NC}"
    echo -e "$FIXES_COMMENT"
    exit 1
fi

echo -e "${GREEN}✅ Smart code fixer completed successfully!${NC}"
echo -e "${BLUE}📊 Fixes suggested: ${SUGGESTIONS_MADE}${NC}" 