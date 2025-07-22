# Advanced Analytics Module

A modular, high-performance analytics visualization component for contribution data.

## Table of Contents

- [Overview](#overview)
- [Directory Structure](#directory-structure)
- [Key Features](#key-features)
- [Performance Optimizations](#performance-optimizations)
- [Browser Compatibility](#browser-compatibility)
- [Usage](#usage)
- [Migration Guide](#migration-guide)

## Overview

The Advanced Analytics module provides advanced visualization capabilities for analyzing contributor data over time. It displays metrics such as total events, MRs created, MRs approved, and repository pushes, with trend indicators and interactive filtering.

## Directory Structure

```
public/js/charts/advanced-analytics/
├── common/                   # Shared utilities and constants
│   ├── constants.js          # Centralized constants
│   ├── DOMHelper.js          # DOM manipulation utility
│   ├── logger.js             # Logging utility
│   └── utils.js              # General utilities
├── controller/               # Business logic and event handling
│   └── Controller.js         # Main controller
├── data/                     # Data processing and transformation
│   └── DataService.js        # Data processing service
├── state/                    # State management
│   └── StateStore.js         # State store
├── view/                     # UI components and rendering
│   ├── template.html         # HTML template
│   ├── TemplateLoader.js     # Template loading utility
│   ├── TooltipManager.js     # Tooltip initialization using IntersectionObserver
│   ├── ViewRenderer.js       # View rendering logic
│   ├── ViewRendererHelpers.js # Helper functions for view rendering
│   └── VirtualScroller.js    # Virtual scrolling implementation
├── index.js                  # Main entry point
└── README.md                 # Documentation
```

## Key Features

- **Modular Architecture**: Separated into data, state, view, and controller layers
- **Performance Optimized**: Virtual scrolling, batched rendering, and lazy tooltip initialization
- **Responsive Design**: Adapts to different screen sizes with optimizations for mobile
- **Interactive Filtering**: Filter by squad, contributor name, and other criteria
- **Trend Visualization**: Shows trends between months with visual indicators
- **Configurable**: Easy to customize and extend

## Performance Optimizations

The module includes several performance optimizations:

- **Virtual Scrolling**: Only renders visible table rows, significantly improving performance for large datasets using `VirtualScroller`
- **DOM Caching**: `DOMHelper` caches DOM queries to reduce expensive DOM operations
- **Lazy Tooltip Initialization**: Uses `IntersectionObserver` to initialize tooltips only when they come into view
- **Debounced and Throttled Events**: Optimized event handlers with debouncing and throttling to avoid performance degradation
- **Batched Rendering**: Splits rendering operations into smaller batches to avoid UI freezing
- **Memoization**: Caches results of expensive calculations to avoid redundant processing
- **Reduced Console Logging**: Configurable logging levels to minimize performance impact in production

## Browser Compatibility

- Chrome (latest 2 versions)
- Firefox (latest 2 versions)
- Safari (latest 2 versions)
- Edge (latest 2 versions)

## Usage

Include the script in your HTML:

```html
<div id="advanced-analytics-container"></div>
<script type="module">
  import AdvancedAnalytics from './public/js/charts/advanced-analytics/index.js';
  
  // Create instance
  const analytics = new AdvancedAnalytics();
  
  // Initialize with data
  analytics.initialize({
    contributionData: yourData,
    containerId: 'advanced-analytics-container'
  });
</script>
```

Or use the migrator for legacy support:

```html
<script src="./public/js/charts/advanced-analytics-migrator.js"></script>
<script>
  // Legacy API support
  AdvancedAnalytics.initialize(yourData, 'advanced-analytics-container');
</script>
```

## Migration Guide

The migrator provides backward compatibility with the original implementation. It maps legacy function calls to their modular equivalents while providing a path to gradually adopt the new architecture.

Feature flags control whether to use the new implementation:

```js
// Enable via URL parameter
// ?useNewAnalytics=true

// Enable via localStorage
localStorage.setItem('useNewAnalytics', 'true');
```

## Development

### Code Standards

- Use ES6+ features with named exports
- Prefer `const` over `let` where appropriate
- Add JSDoc comments for functions
- Use consistent naming conventions
- Break large functions into smaller, focused helpers
- Use proper error handling

### Performance Best Practices

- Cache DOM elements when possible
- Use virtual rendering for large datasets
- Optimize event handlers with debounce/throttle
- Lazy-load resources when appropriate
- Batch DOM updates
- Use IntersectionObserver for visibility-dependent operations

## Data Format

The module expects contribution data in the following format:

```js
[
  {
    name: "Contributor Name",
    squad: "Squad Name",
    year: 2023,
    month: 5,
    totalEvents: 157,
    mrCreated: 25,
    mrApproved: 35,
    repoPushes: 97,
    active: true
  },
  // More data entries...
]
```

## Features

- **Performance Optimizations**:
  - Batch processing and rendering
  - DOM fragment usage
  - Debounced event handlers
  - Cached DOM queries
  - Optimized tooltip initialization

- **Improved Architecture**:
  - Separation of concerns
  - Clean, modular code
  - Proper event delegation
  - State management
  - Memory leak prevention

- **Enhanced UX**:
  - Detailed tooltips
  - Visual drop indicators
  - Keyboard navigation
  - Responsive design
  - Dark mode support

- **Accessibility**:
  - ARIA attributes
  - Keyboard focus management
  - Non-color indicators
  - Screen reader support

## Customization

### Styling

The module uses CSS custom properties for easy theming. Add your own styles by targeting the component selectors or overriding the CSS variables.

### Configuration

Additional configuration options can be passed to the `initialize` method:

```js
analytics.initialize({
  contributionData: myContributionData,
  containerId: 'custom-container',
  // Other options...
});
```

## Development

### Adding Features

To extend the module:

1. Identify the appropriate layer for your feature
2. Add your code to the relevant component
3. Update the public API in `index.js` if needed
4. Add tests for your feature

### Testing

The module should be tested using Jest:

```
npm test
```

## Future Improvements

- Virtual scrolling for large datasets
- Intersection Observer for tooltips
- Data export in multiple formats
- More visualization options (charts, graphs)
- TypeScript conversion for better type safety 