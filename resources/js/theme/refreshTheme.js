// resources/js/theme/refreshTheme.js

/**
 * Function to refresh the theme style CSS
 * This helps prevent browser caching issues when the theme is updated
 */
export function refreshThemeStyle() {
  // Find the theme style link element
  const themeStyleLink = document.getElementById('theme-style-css');

  if (themeStyleLink) {
    // Get the current href
    let currentHref = themeStyleLink.getAttribute('href');

    // Add or update cache-busting parameter
    if (currentHref.includes('?')) {
      // Remove existing cache-busting parameter if it exists
      currentHref = currentHref.split('?')[0];
    }

    // Append a cache-busting parameter with the current timestamp
    const newHref = `${currentHref}?t=${Date.now()}`;

    // Set the new href to force reload
    themeStyleLink.setAttribute('href', newHref);
  }
}

// Add event listener for theme saved events
document.addEventListener('theme-saved', () => {
  // Add a small delay to ensure the server has processed the change
  setTimeout(() => {
    refreshThemeStyle();
  }, 300);
});

// Initialize the refresh handler when the document loads
document.addEventListener('DOMContentLoaded', () => {
  console.log('Theme refresh handler initialized');
});
