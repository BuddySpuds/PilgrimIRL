# PilgrimIRL Theme - Build Process

## Quick Start

### Prerequisites
- Node.js 18+ and npm
- WordPress 6.0+
- PHP 7.4+

### Installation

```bash
# Navigate to theme directory
cd wp-content/themes/pilgrimirl

# Install dependencies
npm install

# Build production assets
npm run production
```

## Available Commands

### Development
```bash
npm run watch        # Watch files and rebuild on changes
npm run build        # Build all assets once
```

### Production
```bash
npm run production   # Clean and build optimized assets for production
npm run clean        # Remove all minified files
```

### Code Quality
```bash
npm run lint:js      # Lint JavaScript files
npm run lint:css     # Lint CSS files
npm run format       # Format code with Prettier
```

### Individual Builds
```bash
npm run build:css    # Build CSS only
npm run build:js     # Build JavaScript only
npm run minify:css   # Minify CSS files
npm run minify:js    # Minify JavaScript files
```

## File Structure

```
pilgrimirl/
├── css/
│   ├── *.css           # Source CSS files
│   └── *.min.css       # Minified CSS (auto-generated)
├── js/
│   ├── *.js            # Source JavaScript files
│   └── *.min.js        # Minified JavaScript (auto-generated)
├── includes/           # PHP utilities
├── _dev-tools/         # Development utilities (not loaded in production)
└── node_modules/       # npm dependencies (gitignored)
```

## Development Workflow

1. **Start watching files:**
   ```bash
   npm run watch
   ```

2. **Make changes to source files**
   - Edit `css/*.css` files
   - Edit `js/*.js` files
   - Changes auto-compile to `.min.*` versions

3. **Test your changes** at http://localhost:10028/

4. **Before committing:**
   ```bash
   npm run lint:js
   npm run lint:css
   npm run format
   ```

5. **Before deployment:**
   ```bash
   npm run production
   ```

## Production Deployment

The theme uses minified assets in production. Make sure to:

1. Run `npm run production` before deploying
2. Commit both source and minified files to Git
3. Theme automatically loads `.min.*` files when `WP_DEBUG` is false

## Google Maps Setup

1. Get API key from [Google Cloud Console](https://console.cloud.google.com/)
2. In WordPress Admin: **Settings → General**
3. Add API key to "Google Maps API Key" field
4. Enable these APIs:
   - Maps JavaScript API
   - Geocoding API
   - Places API

## Debugging

### Enable Debug Mode
Edit `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### JavaScript Debugging
- Debug logs only show when accessing from localhost
- Check browser console for `[PilgrimIRL]` prefixed messages
- Use `PilgrimDebug.log()` instead of `console.log()`

### PHP Debugging
- Check `/wp-content/debug.log` for PHP errors
- Use `error_log()` for debugging
- XDebug supported in Local by Flywheel

## Performance Optimization

### Caching
- Install WP Super Cache or W3 Total Cache
- Enable object caching for filter queries
- Configure browser caching in `.htaccess`

### Images
- Optimize images before upload
- Use WebP format when possible
- Enable lazy loading (built into theme)

### Database
- Regular optimization via phpMyAdmin
- Add indexes for geo queries (see `optimize-database.sql`)

## Troubleshooting

### npm install fails
```bash
rm -rf node_modules package-lock.json
npm cache clean --force
npm install
```

### Build fails
```bash
npm run clean
npm run production
```

### Maps not loading
- Check Google Maps API key in WP Admin
- Verify API restrictions allow localhost
- Check browser console for errors

## Browser Support

- Chrome/Edge (last 2 versions)
- Firefox (last 2 versions)
- Safari (last 2 versions)
- Mobile browsers (iOS Safari, Chrome Android)

## Contributing

1. Create feature branch
2. Make changes
3. Run linters and formatters
4. Test on localhost
5. Commit with descriptive message
6. Create pull request

## License

GPL v2 or later
