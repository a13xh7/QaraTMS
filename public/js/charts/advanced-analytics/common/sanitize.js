/**
 * sanitize.js
 * Comprehensive sanitization utilities to prevent XSS attacks and ensure data integrity
 */

/**
 * Escapes HTML special characters to prevent XSS attacks
 * @param {string} unsafe - Potentially unsafe string 
 * @returns {string} Escaped string safe for insertion in HTML
 */
export function escapeHTML(unsafe) {
    if (unsafe === null || unsafe === undefined) {
        return '';
    }
    
    if (typeof unsafe !== 'string') {
        unsafe = String(unsafe);
    }
    
    return unsafe
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

/**
 * Sanitizes a string for use in HTML attributes
 * @param {string} value - Attribute value to sanitize
 * @returns {string} Sanitized attribute value
 */
export function sanitizeAttribute(value) {
    if (value === null || value === undefined) {
        return '';
    }
    
    if (typeof value !== 'string') {
        value = String(value);
    }
    
    // Remove any JavaScript protocol or data URI
    value = value.replace(/^\s*javascript:/i, '')
                 .replace(/^\s*data:/i, '')
                 .replace(/^\s*vbscript:/i, '');
    
    return escapeHTML(value);
}

/**
 * Sanitizes user input for safe insertion into the DOM as text content
 * @param {HTMLElement} element - Element to set text content on
 * @param {string} content - Content to sanitize and set
 */
export function setTextContent(element, content) {
    if (!element) return;
    
    // Use textContent to ensure content is treated as text, not HTML
    element.textContent = content;
}

/**
 * Creates a sanitized HTML string that's safe to use with innerHTML
 * Allows only a limited subset of HTML tags and attributes
 * @param {string} html - Potentially unsafe HTML string
 * @returns {string} Sanitized HTML string
 */
export function sanitizeHTML(html) {
    if (html === null || html === undefined) {
        return '';
    }
    
    if (typeof html !== 'string') {
        html = String(html);
    }
    
    // Create a temporary DOM element
    const tempElement = document.createElement('div');
    tempElement.innerHTML = html;
    
    // Define allowed tags and attributes
    const allowedTags = ['a', 'b', 'br', 'div', 'em', 'i', 'li', 'ol', 'p', 'span', 'strong', 'ul'];
    const allowedAttributes = {
        'a': ['href', 'title', 'target'],
        'div': ['class'],
        'span': ['class'],
        'p': ['class'],
        'li': ['class']
    };
    
    // Remove disallowed elements
    const removeDisallowedElements = (element) => {
        const childNodes = Array.from(element.childNodes);
        
        childNodes.forEach(node => {
            if (node.nodeType === Node.ELEMENT_NODE) {
                const tagName = node.tagName.toLowerCase();
                
                if (!allowedTags.includes(tagName)) {
                    // Replace with text content
                    const text = document.createTextNode(node.textContent);
                    node.parentNode.replaceChild(text, node);
                } else {
                    // Process allowed element's attributes
                    const allowedAttrs = allowedAttributes[tagName] || [];
                    
                    // Remove disallowed attributes
                    Array.from(node.attributes).forEach(attr => {
                        const attrName = attr.name.toLowerCase();
                        
                        if (!allowedAttrs.includes(attrName)) {
                            node.removeAttribute(attrName);
                        } else if (attrName === 'href') {
                            // Special handling for links - ensure they're safe
                            const value = attr.value.toLowerCase().trim();
                            if (value.startsWith('javascript:') || value.startsWith('data:')) {
                                node.setAttribute('href', '#');
                            }
                            
                            // Force target="_blank" and rel="noopener noreferrer" for all external links
                            if (value.startsWith('http')) {
                                node.setAttribute('target', '_blank');
                                node.setAttribute('rel', 'noopener noreferrer');
                            }
                        }
                    });
                    
                    // Process children recursively
                    removeDisallowedElements(node);
                }
            }
        });
    };
    
    // Clean the content
    removeDisallowedElements(tempElement);
    
    return tempElement.innerHTML;
}

/**
 * Sanitizes user input for safe usage in CSV exports
 * @param {string} value - Value to sanitize for CSV
 * @returns {string} Sanitized value safe for CSV
 */
export function sanitizeForCSV(value) {
    if (value === null || value === undefined) {
        return '';
    }
    
    const stringValue = String(value);
    
    // Escape quotes and remove any characters that could break CSV format
    return stringValue
        .replace(/"/g, '""') // Double quotes for CSV escaping
        .replace(/[\n\r]+/g, ' ') // Replace newlines with spaces
        .replace(/\t/g, ' '); // Replace tabs with spaces
}

/**
 * Creates a properly quoted CSV field value
 * @param {string} value - Value to format as CSV field
 * @returns {string} Formatted CSV field
 */
export function csvField(value) {
    return `"${sanitizeForCSV(value)}"`;
}

/**
 * Sanitizes user input for safe usage in JSON
 * @param {any} value - Value to sanitize for JSON
 * @returns {string} Sanitized JSON string
 */
export function sanitizeForJSON(value) {
    try {
        // Convert to string and back through JSON to ensure it's valid
        const jsonString = JSON.stringify(value);
        return jsonString;
    } catch (e) {
        // If there's any error, return a safe empty JSON object/array
        return Array.isArray(value) ? '[]' : '{}';
    }
}

/**
 * Sanitizes a filename to ensure it's safe for filesystem operations
 * @param {string} filename - Filename to sanitize
 * @returns {string} Sanitized filename
 */
export function sanitizeFilename(filename) {
    if (!filename) return '';
    
    // Remove path traversal characters and unsafe characters
    return filename
        .replace(/[/\\?%*:|"<>]/g, '-') // Replace unsafe characters with dash
        .replace(/\.+/g, '.') // Collapse multiple dots
        .replace(/^\.+|\.+$/g, '') // Remove leading/trailing dots
        .trim();
}

/**
 * Sanitizes a JavaScript object by deep copying and removing functions
 * Useful for ensuring objects are safe for serialization
 * @param {Object} obj - Object to sanitize
 * @returns {Object} Sanitized object
 */
export function sanitizeObject(obj) {
    if (!obj || typeof obj !== 'object') {
        return obj;
    }
    
    // Handle arrays
    if (Array.isArray(obj)) {
        return obj.map(item => sanitizeObject(item));
    }
    
    // Handle date objects
    if (obj instanceof Date) {
        return new Date(obj);
    }
    
    // Handle regular objects
    const sanitized = {};
    for (const key in obj) {
        if (Object.prototype.hasOwnProperty.call(obj, key)) {
            const value = obj[key];
            
            // Skip functions
            if (typeof value === 'function') {
                continue;
            }
            
            // Recursively sanitize nested objects
            sanitized[key] = sanitizeObject(value);
        }
    }
    
    return sanitized;
}

/**
 * Main sanitize function that automatically handles different types of input
 * @param {any} input - Input to sanitize
 * @param {string} [context='text'] - Context of sanitization ('text', 'html', 'attribute', etc.)
 * @returns {any} Sanitized input
 */
export default function sanitize(input, context = 'text') {
    if (input === null || input === undefined) {
        return '';
    }
    
    switch (context) {
        case 'html':
            return sanitizeHTML(input);
        case 'attribute':
            return sanitizeAttribute(input);
        case 'csv':
            return sanitizeForCSV(input);
        case 'json':
            return sanitizeForJSON(input);
        case 'filename':
            return sanitizeFilename(input);
        case 'object':
            return sanitizeObject(input);
        case 'text':
        default:
            return escapeHTML(input);
    }
} 