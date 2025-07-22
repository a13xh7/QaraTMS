#!/bin/bash

# AI-Powered Code Review Script for GitLab CI
# This script analyzes code and provides intelligent suggestions and fixes

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
NC='\033[0m' # No Color

echo -e "${BLUE}🤖 Starting AI-Powered Code Review...${NC}"

# Check if we're in a merge request
if [ "$CI_PIPELINE_SOURCE" != "merge_request_event" ]; then
    echo "Not a merge request, skipping AI review"
    exit 0
fi

# Check if required environment variables are set
if [ -z "$GITLAB_TOKEN" ]; then
    echo -e "${RED}❌ GITLAB_TOKEN is not set. Cannot post review comments.${NC}"
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

# Initialize variables
REVIEW_COMMENT=""
SUGGESTIONS=""
CRITICAL_ISSUES=0
WARNINGS=0
IMPROVEMENTS=0

# Function to add section to review comment
add_section() {
    local title="$1"
    local content="$2"
    REVIEW_COMMENT="${REVIEW_COMMENT}\n\n${content}"
}

# Function to add suggestion with code
add_suggestion() {
    local file="$1"
    local line="$2"
    local issue="$3"
    local suggestion="$4"
    local priority="$5"
    
    SUGGESTIONS="${SUGGESTIONS}\n\n### ${priority} ${file}:${line}\n**Issue:** ${issue}\n\n**Suggestion:**\n\`\`\`php\n${suggestion}\n\`\`\`"
}

# Function to analyze PHP files for common issues
analyze_php_file() {
    local file="$1"
    local line_number=0
    
    # Check if file exists and is readable
    if [ ! -f "$file" ] || [ ! -r "$file" ]; then
        echo -e "${YELLOW}⚠️ Cannot read file: $file${NC}"
        return
    fi
    
    while IFS= read -r line; do
        line_number=$((line_number + 1))
        
        # Check for environment variable usage without validation
        if echo "$line" | grep -q "env('[^']*')" && ! echo "$line" | grep -q "empty\|isset\|throw"; then
            add_suggestion "$file" "$line_number" "Environment variable used without validation" \
                "// Before:\n$line\n\n// After:\nif (empty(env('VARIABLE_NAME'))) {\n    throw new \\InvalidArgumentException('Required environment variable VARIABLE_NAME is not set');\n}\n\$variable = env('VARIABLE_NAME');" \
                "🔧"
            WARNINGS=$((WARNINGS + 1))
        fi
        
        # Check for magic numbers
        if echo "$line" | grep -q "range(2023\|[1,2,3,4,5,6,7,8,9,10,11,12]" && ! echo "$line" | grep -q "const\|private const"; then
            add_suggestion "$file" "$line_number" "Magic numbers should be constants" \
                "// Before:\n$line\n\n// After:\nprivate const START_YEAR = 2023;\nprivate const MONTHS = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];\n\$listOfYears = range(self::START_YEAR, Carbon::now()->year);\n\$listOfMonth = self::MONTHS;" \
                "🔧"
            WARNINGS=$((WARNINGS + 1))
        fi
        
        # Check for missing PHPDoc comments
        if echo "$line" | grep -q "public function\|protected function" && ! echo "$line" | grep -q "/\*\*\|@param\|@return"; then
            local func_name=""
            if [ "$SED_AVAILABLE" = true ]; then
                func_name=$(echo "$line" | sed 's/.*function \([a-zA-Z_][a-zA-Z0-9_]*\).*/\1/')
            else
                # Basic fallback without sed
                func_name=$(echo "$line" | grep -o 'function [a-zA-Z_][a-zA-Z0-9_]*' | cut -d' ' -f2)
            fi
            add_suggestion "$file" "$line_number" "Missing PHPDoc comment for public method" \
                "/**\n * ${func_name} description\n *\n * @param mixed \$param Description\n * @return mixed Description\n */\n$line" \
                "📝"
            WARNINGS=$((WARNINGS + 1))
        fi
        
        # Check for generic exception handling
        if echo "$line" | grep -q "catch (\\\\Exception" && ! echo "$line" | grep -q "catch (\\\\GuzzleHttp\\\\Exception\|catch (\\\\InvalidArgumentException"; then
            add_suggestion "$file" "$line_number" "Generic exception handling should be more specific" \
                "// Before:\n$line\n\n// After:\ntry {\n    // Your code\n} catch (\\\\GuzzleHttp\\\\Exception\\\\RequestException \$e) {\n    // Handle API errors\n} catch (\\\\InvalidArgumentException \$e) {\n    // Handle validation errors\n} catch (\\\\Exception \$e) {\n    // Handle other unexpected errors\n}" \
                "🛡️"
            WARNINGS=$((WARNINGS + 1))
        fi
        
        # Check for potential security issues
        if echo "$line" | grep -q "file_get_contents\|include\|require" && echo "$line" | grep -q "\$_GET\|\$_POST\|\$_REQUEST"; then
            add_suggestion "$file" "$line_number" "Potential security vulnerability - user input used in file operations" \
                "// Before:\n$line\n\n// After:\n// Validate and sanitize user input\n\$userInput = filter_input(INPUT_GET, 'param', FILTER_SANITIZE_STRING);\nif (!is_string(\$userInput) || !preg_match('/^[a-zA-Z0-9_-]+$/', \$userInput)) {\n    throw new \\InvalidArgumentException('Invalid input');\n}" \
                "🚨"
            CRITICAL_ISSUES=$((CRITICAL_ISSUES + 1))
        fi
        
        # Check for performance issues
        if echo "$line" | grep -q "foreach.*query\|while.*query" && ! echo "$line" | grep -q "chunk\|cursor"; then
            add_suggestion "$file" "$line_number" "Potential N+1 query problem" \
                "// Before:\n$line\n\n// After:\n// Use eager loading or chunking\nModel::with('relation')->chunk(100, function (\$items) {\n    foreach (\$items as \$item) {\n        // Process item\n    }\n});" \
                "⚡"
            WARNINGS=$((WARNINGS + 1))
        fi
        
    done < "$file"
}

# Function to analyze Laravel-specific patterns
analyze_laravel_patterns() {
    local file="$1"
    
    # Check if file exists and is readable
    if [ ! -f "$file" ] || [ ! -r "$file" ]; then
        return
    fi
    
    # Check for proper model usage
    if grep -q "DB::table\|DB::select\|DB::insert" "$file" && ! grep -q "use.*Model" "$file"; then
        add_suggestion "$file" "N/A" "Consider using Eloquent models instead of raw DB queries" \
            "// Before:\nDB::table('users')->where('id', \$id)->get();\n\n// After:\nUser::where('id', \$id)->get();\n\n// Benefits:\n// - Better type safety\n// - Easier to maintain\n// - Built-in relationships" \
            "🎯"
        IMPROVEMENTS=$((IMPROVEMENTS + 1))
    fi
    
    # Check for proper validation
    if grep -q "Request::all()\|request()->all()" "$file" && ! grep -q "validate\|rules\|messages"; then
        add_suggestion "$file" "N/A" "Add proper request validation" \
            "// Before:\n\$data = request()->all();\n\n// After:\n\$data = \$request->validate([\n    'name' => 'required|string|max:255',\n    'email' => 'required|email|unique:users',\n    'password' => 'required|min:8|confirmed'\n]);" \
            "✅"
        WARNINGS=$((WARNINGS + 1))
    fi
    
    # Check for proper error handling
    if grep -q "try.*catch" "$file" && ! grep -q "Log::error\|Log::warning"; then
        add_suggestion "$file" "N/A" "Add proper logging for exceptions" \
            "// Before:\ntry {\n    // Code\n} catch (Exception \$e) {\n    // Handle error\n}\n\n// After:\ntry {\n    // Code\n} catch (Exception \$e) {\n    Log::error('Operation failed', [\n        'message' => \$e->getMessage(),\n        'file' => \$e->getFile(),\n        'line' => \$e->getLine(),\n        'trace' => \$e->getTraceAsString()\n    ]);\n    // Handle error\n}" \
            "📝"
        WARNINGS=$((WARNINGS + 1))
    fi
}

# Function to generate intelligent review summary
generate_review_summary() {
    local summary=""
    
    if [ "$CRITICAL_ISSUES" -gt 0 ]; then
        summary="${summary}🚨 **${CRITICAL_ISSUES} critical issues** found\n"
    fi
    if [ "$WARNINGS" -gt 0 ]; then
        summary="${summary}⚠️ **${WARNINGS} warnings** found\n"
    fi
    if [ "$IMPROVEMENTS" -gt 0 ]; then
        summary="${summary}💡 **${IMPROVEMENTS} improvement suggestions** found\n"
    fi
    if [ "$((CRITICAL_ISSUES + WARNINGS + IMPROVEMENTS))" -eq 0 ]; then
        summary="✅ **Excellent code quality!** No issues found 🎉"
    fi
    
    add_section "📊 AI Review Summary" "${summary}"
}

# Function to generate priority-based recommendations
generate_recommendations() {
    local recommendations=""
    
    if [ "$CRITICAL_ISSUES" -gt 0 ]; then
        recommendations="${recommendations}- [ ] **🔴 CRITICAL**: Fix security vulnerabilities immediately\n"
        recommendations="${recommendations}- [ ] **🔴 CRITICAL**: Address potential data loss issues\n"
    fi
    if [ "$WARNINGS" -gt 0 ]; then
        recommendations="${recommendations}- [ ] **🟡 HIGH**: Add environment variable validation\n"
        recommendations="${recommendations}- [ ] **🟡 HIGH**: Replace magic numbers with constants\n"
        recommendations="${recommendations}- [ ] **🟡 HIGH**: Add missing PHPDoc comments\n"
        recommendations="${recommendations}- [ ] **🟡 HIGH**: Improve exception handling\n"
    fi
    if [ "$IMPROVEMENTS" -gt 0 ]; then
        recommendations="${recommendations}- [ ] **🟢 MEDIUM**: Consider using Eloquent models\n"
        recommendations="${recommendations}- [ ] **🟢 MEDIUM**: Add request validation\n"
        recommendations="${recommendations}- [ ] **🟢 MEDIUM**: Improve logging\n"
    fi
    
    if [ -n "$recommendations" ]; then
        add_section "📋 Priority Recommendations" "${recommendations}"
    fi
}

# Main analysis process
echo -e "${YELLOW}🔍 Analyzing PHP files...${NC}"

# Check if app directory exists
if [ ! -d "app" ]; then
    echo -e "${YELLOW}⚠️ app directory not found, checking current directory for PHP files...${NC}"
    PHP_FILES=$(find . -name "*.php" -type f 2>/dev/null || echo "")
else
    PHP_FILES=$(find app/ -name "*.php" -type f 2>/dev/null || echo "")
fi

if [ -z "$PHP_FILES" ]; then
    echo -e "${YELLOW}⚠️ No PHP files found to analyze${NC}"
    add_section "📊 AI Review Summary" "✅ **No PHP files found to analyze**\n\nThis review found no PHP files in the expected locations. If this is unexpected, please check the file structure."
else
    echo "$PHP_FILES" | while read -r file; do
        if [ -n "$file" ]; then
            echo -e "${PURPLE}Analyzing: $file${NC}"
            analyze_php_file "$file"
            analyze_laravel_patterns "$file"
        fi
    done
fi

# Generate review sections
REVIEW_COMMENT="# 🤖 AI Review Summary\n"

# Add summary
if [ "$CRITICAL_ISSUES" -gt 0 ]; then
    REVIEW_COMMENT+="\n🚨 **Critical issues found!**\n"
fi
if [ "$WARNINGS" -gt 0 ]; then
    REVIEW_COMMENT+="\n⚠️ **Warnings detected!**\n"
fi
if [ "$IMPROVEMENTS" -gt 0 ]; then
    REVIEW_COMMENT+="\n💡 **Improvement suggestions available.**\n"
fi
if [ "$((CRITICAL_ISSUES + WARNINGS + IMPROVEMENTS))" -eq 0 ]; then
    REVIEW_COMMENT+="\n✅ **Excellent code quality!** No issues found 🎉\n"
fi

REVIEW_COMMENT+="\n---\n\n## 📚 Best Practices Reminder\n\n### Laravel Best Practices\n- Use Eloquent models instead of raw DB queries\n- Always validate user input\n- Use proper error handling and logging\n- Follow **PSR-12** coding standards\n- Add comprehensive PHPDoc comments\n- Use dependency injection\n- Implement proper caching strategies\n\n### Security Best Practices\n- Validate and sanitize all user inputs\n- Use prepared statements for database queries\n- Implement proper authentication and authorization\n- Keep dependencies updated\n- Use environment variables for sensitive data\n- Implement rate limiting for APIs\n\n---\n\n## ℹ️ About This Review\n\nThis AI-powered review was automatically generated by GitLab CI.\n\n**Review Categories:**\n- 🚨 **Critical:** Security vulnerabilities, data loss risks\n- ⚠️ **Warning:** Code quality issues, best practice violations\n- 💡 **Improvement:** Performance optimizations, maintainability\n- 🔧 **Refactor:** Code structure improvements\n- 📝 **Documentation:** Missing comments and documentation\n\n**Environment Info:**\nShell Executor: ${SHELL:-unknown}\nAvailable Tools: curl=${CURL_AVAILABLE}, jq=${JQ_AVAILABLE}, git=${GIT_AVAILABLE}\nPHP Files Analyzed: $(echo "$PHP_FILES" | wc -l)\n"

# Generate review sections
generate_review_summary
generate_recommendations

# Add suggestions section if any found
if [ -n "$SUGGESTIONS" ]; then
    add_section "💡 Specific Suggestions" "${SUGGESTIONS}"
fi

# Post the review comment
echo -e "${GREEN}📝 Posting AI-powered review comment...${NC}"

# Escape the comment for JSON (simplified version)
if [ "$SED_AVAILABLE" = true ]; then
    ESCAPED_COMMENT=$(echo -e "$REVIEW_COMMENT" | sed 's/"/\\"/g' | tr '\n' ' ')
else
    # Basic fallback without sed
    ESCAPED_COMMENT=$(echo -e "$REVIEW_COMMENT" | tr '"' '\\"' | tr '\n' ' ')
fi

# Post to GitLab MR
if [ "$CURL_AVAILABLE" = true ]; then
    curl --request POST \
      --header "PRIVATE-TOKEN: $GITLAB_TOKEN" \
      --header "Content-Type: application/json" \
      --data "{\"body\":\"$ESCAPED_COMMENT\"}" \
      "https://gitlab.com/api/v4/projects/$CI_PROJECT_ID/merge_requests/$CI_MERGE_REQUEST_IID/notes" || {
        echo -e "${RED}❌ Failed to post AI review comment${NC}"
        echo -e "${YELLOW}Debug info:${NC}"
        echo "GITLAB_TOKEN: ${GITLAB_TOKEN:0:10}..."
        echo "CI_PROJECT_ID: $CI_PROJECT_ID"
        echo "CI_MERGE_REQUEST_IID: $CI_MERGE_REQUEST_IID"
        echo -e "${YELLOW}Comment preview:${NC}"
        echo -e "$REVIEW_COMMENT"
        exit 1
    }
else
    echo -e "${RED}❌ curl not available, cannot post review comment${NC}"
    echo -e "${YELLOW}Comment that would have been posted:${NC}"
    echo -e "$REVIEW_COMMENT"
    exit 1
fi

echo -e "${GREEN}✅ AI-powered review completed successfully!${NC}"
echo -e "${BLUE}📊 Issues found: ${CRITICAL_ISSUES} critical, ${WARNINGS} warnings, ${IMPROVEMENTS} improvements${NC}" 