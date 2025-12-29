# Bulk URL Shortener Plugin

A YOURLS plugin that allows you to create multiple short URLs at once using a pattern-based approach.

## Features

- **Pattern-based URL creation**: Use `{value}` as a placeholder in your URL pattern
- **Range support**: Specify a start and end number (e.g., 1 to 50)
- **Custom keyword patterns**: Optionally specify a pattern for custom short URLs
- **Progress tracking**: Real-time progress bar and results display
- **Error handling**: Shows which URLs were created successfully and which failed

## Usage

1. Go to the YOURLS admin panel
2. Click "Switch to Advanced Mode" below the URL shortening form
3. Enter your URL pattern with `{value}` placeholder (e.g., `https://example.com/sunbed/{value}`)
4. Specify the start and end numbers (e.g., 1 to 50)
5. Optionally enter a keyword pattern (e.g., `sunbed{value}`)
6. Click "Create Bulk URLs"

## Examples

### Example 1: Sunbeds
- **URL Pattern**: `https://example.com/sunbed/{value}`
- **Start**: 1
- **End**: 50
- **Keyword Pattern**: `sunbed{value}`

This will create:
- `https://example.com/sunbed/1` → `yourls.site/sunbed1`
- `https://example.com/sunbed/2` → `yourls.site/sunbed2`
- ... and so on up to 50

### Example 2: Products
- **URL Pattern**: `https://shop.example.com/product/{value}`
- **Start**: 100
- **End**: 200
- **Keyword Pattern**: (leave empty for auto-generated)

This will create URLs from product 100 to 200 with auto-generated keywords.

## Notes

- Maximum recommended range: 1000 URLs at a time
- The plugin processes URLs sequentially to avoid overwhelming the server
- Progress is shown in real-time
- Only the last 20 results are displayed in the results area
- The page will automatically refresh after successful bulk creation

