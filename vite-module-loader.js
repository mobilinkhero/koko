import fs from 'fs/promises';
import path from 'path';
import { pathToFileURL } from 'url';

/**
 * Check if a file has meaningful content (not just empty or whitespace)
 * @param {string} filePath - The full path to the file
 * @returns {Promise<boolean>} - True if file has content, false otherwise
 */
async function hasContent(filePath) {
  try {
    const content = await fs.readFile(filePath, 'utf-8');
    return content.trim().length > 0;
  } catch (error) {
    return false;
  }
}

async function collectModuleAssetsPaths(paths, modulesPath) {
  modulesPath = path.join(__dirname, modulesPath);

  const moduleStatusesPath = path.join(__dirname, 'modules_statuses.json');

  try {
    // Read module_statuses.json
    const moduleStatusesContent = await fs.readFile(moduleStatusesPath, 'utf-8');
    const moduleStatuses = JSON.parse(moduleStatusesContent);

    // Read module directories
    const moduleDirectories = await fs.readdir(modulesPath);

    for (const moduleDir of moduleDirectories) {
      if (moduleDir === '.DS_Store') {
        // Skip .DS_Store directory
        continue;
      }

      // Check if the module is enabled (status is true)
      if (moduleStatuses[moduleDir] === true) {
        const viteConfigPath = path.join(modulesPath, moduleDir, 'vite.config.js');

        try {
          await fs.access(viteConfigPath);
          // Convert to a file URL for Windows compatibility
          const moduleConfigURL = pathToFileURL(viteConfigPath);

          // Import the module-specific Vite configuration
          const moduleConfig = await import(moduleConfigURL.href);

          if (moduleConfig.paths && Array.isArray(moduleConfig.paths)) {
            // Validate each path to ensure files exist and have content
            for (const assetPath of moduleConfig.paths) {
              const fullPath = path.join(__dirname, assetPath);
              try {
                const stats = await fs.stat(fullPath);
                const fileHasContent = await hasContent(fullPath);

                if (stats.isFile() && fileHasContent) {
                  paths.push(assetPath);
                }
              } catch (error) {
                // Asset file not found, skip silently
              }
            }
          }
        } catch (error) {
          // vite.config.js does not exist, skip this module
        }
      }
    }
  } catch (error) {
    console.error(`Error reading module statuses or module configurations: ${error}`);
  }

  return paths;
}

export default collectModuleAssetsPaths;
