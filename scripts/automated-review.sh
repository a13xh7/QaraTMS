#!/bin/bash

# Automated Code Review Script for GitLab CI
# This script analyzes code quality reports and posts intelligent review comments

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🤖 Starting Automated Code Review...${NC}"

# Check if we're in a merge request
if [ "$CI_PIPELINE_SOURCE" != "merge_request_event" ]; then
    echo "Not a merge request, skipping automated review"
    exit 0
fi

# Initialize variables
REVIEW_COMMENT=""
ISSUES_FOUND=0
CRITICAL_ISSUES=0
WARNINGS=0

# Function to add section to review comment
add_section() {
    local title="$1"
    local content="$2"
    REVIEW_COMMENT="${REVIEW_COMMENT}\n\n## ${title}\n${content}"
}

# Function to count issues in XML report
count_issues() {
    local file="$1"
    if [ -f "$file" ]; then
        xmlstarlet sel -t -v "count(//error)" "$file" 2>/dev/null || echo "0"
    else
        echo "0"
    fi
}

# Function to extract specific issues
extract_issues() {
    local file="$1"
    local max_issues="$2"
    if [ -f "$file" ]; then
        xmlstarlet sel -t -m "//error[position()<=$max_issues]" -v "concat(@line, ': ', @message)" -n "$file" 2>/dev/null || echo "Unable to parse issues"
    else
        echo "Report file not found"
    fi
}

# Analyze PHP CodeSniffer results
echo -e "${YELLOW}📋 Analyzing PSR-12 compliance...${NC}"
if [ -f "phpcs-report.xml" ]; then
    PSR_ISSUES=$(count_issues "phpcs-report.xml")
    if [ "$PSR_ISSUES" -gt 0 ]; then
        ISSUES_FOUND=$((ISSUES_FOUND + PSR_ISSUES))
        WARNINGS=$((WARNINGS + PSR_ISSUES))
        PSR_SAMPLE=$(extract_issues "phpcs-report.xml" 5)
        add_section "⚠️ PSR-12 Compliance Issues" "Found ${PSR_ISSUES} PSR-12 violations:\n\`\`\`\n${PSR_SAMPLE}\n\`\`\`"
    else
        add_section "✅ PSR-12 Compliance" "All code follows PSR-12 standards"
    fi
else
    add_section "❓ PSR-12 Analysis" "PSR-12 report not available"
fi

# Analyze PHP Mess Detector results
echo -e "${YELLOW}🔍 Analyzing code complexity...${NC}"
if [ -f "phpmd-report.xml" ]; then
    COMPLEXITY_ISSUES=$(count_issues "phpmd-report.xml")
    if [ "$COMPLEXITY_ISSUES" -gt 0 ]; then
        ISSUES_FOUND=$((ISSUES_FOUND + COMPLEXITY_ISSUES))
        WARNINGS=$((WARNINGS + COMPLEXITY_ISSUES))
        COMPLEXITY_SAMPLE=$(extract_issues "phpmd-report.xml" 5)
        add_section "⚠️ Code Complexity Issues" "Found ${COMPLEXITY_ISSUES} complexity issues:\n\`\`\`\n${COMPLEXITY_SAMPLE}\n\`\`\`"
    else
        add_section "✅ Code Complexity" "Code complexity is within acceptable limits"
    fi
else
    add_section "❓ Code Complexity Analysis" "Complexity report not available"
fi

# Analyze PHPStan results
echo -e "${YELLOW}🔬 Analyzing static analysis...${NC}"
if [ -f "phpstan-report.xml" ]; then
    STATIC_ISSUES=$(count_issues "phpstan-report.xml")
    if [ "$STATIC_ISSUES" -gt 0 ]; then
        ISSUES_FOUND=$((ISSUES_FOUND + STATIC_ISSUES))
        CRITICAL_ISSUES=$((CRITICAL_ISSUES + STATIC_ISSUES))
        STATIC_SAMPLE=$(extract_issues "phpstan-report.xml" 5)
        add_section "🚨 Static Analysis Issues" "Found ${STATIC_ISSUES} static analysis issues:\n\`\`\`\n${STATIC_SAMPLE}\n\`\`\`"
    else
        add_section "✅ Static Analysis" "No static analysis issues found"
    fi
else
    add_section "❓ Static Analysis" "Static analysis report not available"
fi

# Analyze security results
echo -e "${YELLOW}🔒 Analyzing security vulnerabilities...${NC}"
if [ -f "security-report.xml" ]; then
    SECURITY_ISSUES=$(count_issues "security-report.xml")
    if [ "$SECURITY_ISSUES" -gt 0 ]; then
        ISSUES_FOUND=$((ISSUES_FOUND + SECURITY_ISSUES))
        CRITICAL_ISSUES=$((CRITICAL_ISSUES + SECURITY_ISSUES))
        SECURITY_SAMPLE=$(extract_issues "security-report.xml" 5)
        add_section "🚨 Security Vulnerabilities" "Found ${SECURITY_ISSUES} security issues:\n\`\`\`\n${SECURITY_SAMPLE}\n\`\`\`"
    else
        add_section "✅ Security Analysis" "No security vulnerabilities detected"
    fi
else
    add_section "❓ Security Analysis" "Security report not available"
fi

# Analyze test coverage
echo -e "${YELLOW}🧪 Analyzing test coverage...${NC}"
if [ -f "coverage.xml" ]; then
    COVERAGE_PERCENT=$(xmlstarlet sel -t -v "//coverage/@line-rate" coverage.xml 2>/dev/null | awk '{printf "%.1f", $1 * 100}' || echo "0")
    if (( $(echo "$COVERAGE_PERCENT < 70" | bc -l) )); then
        WARNINGS=$((WARNINGS + 1))
        add_section "⚠️ Test Coverage" "Test coverage is ${COVERAGE_PERCENT}% (recommended: 70%+)"
    else
        add_section "✅ Test Coverage" "Test coverage is ${COVERAGE_PERCENT}%"
    fi
else
    add_section "❓ Test Coverage" "Coverage report not available"
fi

# Generate summary and recommendations
echo -e "${YELLOW}📊 Generating summary...${NC}"

SUMMARY=""
if [ "$CRITICAL_ISSUES" -gt 0 ]; then
    SUMMARY="${SUMMARY}🚨 **${CRITICAL_ISSUES} critical issues** found\n"
fi
if [ "$WARNINGS" -gt 0 ]; then
    SUMMARY="${SUMMARY}⚠️ **${WARNINGS} warnings** found\n"
fi
if [ "$ISSUES_FOUND" -eq 0 ]; then
    SUMMARY="✅ **No issues found** - Great job! 🎉"
fi

add_section "📊 Summary" "${SUMMARY}"

# Add recommendations based on findings
RECOMMENDATIONS=""
if [ "$CRITICAL_ISSUES" -gt 0 ]; then
    RECOMMENDATIONS="${RECOMMENDATIONS}- [ ] **PRIORITY**: Fix critical static analysis issues\n"
    RECOMMENDATIONS="${RECOMMENDATIONS}- [ ] **PRIORITY**: Address security vulnerabilities\n"
fi
if [ "$WARNINGS" -gt 0 ]; then
    RECOMMENDATIONS="${RECOMMENDATIONS}- [ ] Fix PSR-12 compliance issues\n"
    RECOMMENDATIONS="${RECOMMENDATIONS}- [ ] Reduce code complexity where possible\n"
fi
if [ "$COVERAGE_PERCENT" != "0" ] && (( $(echo "$COVERAGE_PERCENT < 70" | bc -l) )); then
    RECOMMENDATIONS="${RECOMMENDATIONS}- [ ] Add more unit tests to improve coverage\n"
fi

if [ -n "$RECOMMENDATIONS" ]; then
    add_section "📋 Recommendations" "${RECOMMENDATIONS}"
fi

# Add footer
add_section "ℹ️ About This Review" "This review was automatically generated by GitLab CI. For detailed reports, check the pipeline artifacts."

# Post the review comment
echo -e "${GREEN}📝 Posting review comment...${NC}"

# Escape the comment for JSON
ESCAPED_COMMENT=$(echo -e "$REVIEW_COMMENT" | sed 's/"/\\"/g' | sed ':a;N;$!ba;s/\n/\\n/g')

# Post to GitLab MR
curl --request POST \
  --header "PRIVATE-TOKEN: $GITLAB_TOKEN" \
  --header "Content-Type: application/json" \
  --data "{\"body\":\"$ESCAPED_COMMENT\"}" \
  "https://gitlab.com/api/v4/projects/$CI_PROJECT_ID/merge_requests/$CI_MERGE_REQUEST_IID/notes" || {
    echo -e "${RED}❌ Failed to post review comment${NC}"
    exit 1
}

echo -e "${GREEN}✅ Automated review completed successfully!${NC}"
echo -e "${BLUE}📊 Issues found: ${ISSUES_FOUND} (${CRITICAL_ISSUES} critical, ${WARNINGS} warnings)${NC}" 