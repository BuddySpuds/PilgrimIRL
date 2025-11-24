# Irish High Crosses Dataset

This repository contains geocoded data for Irish High Crosses, scraped from [Megalithic Ireland](https://www.megalithicireland.com/High%20Cross%20Home.htm) and enriched with geographic coordinates.

## Files

- `high_crosses.json` - JSON format dataset containing names, counties, and coordinates for each High Cross
- `high_crosses.csv` - CSV format of the same data for easy import into spreadsheets
- `high_crosses_map.html` - Interactive web map displaying all High Crosses

## Data Collection Process

1. The list of High Crosses was extracted from the Megalithic Ireland website
2. Each location was geocoded using the Google Maps Geocoding API
3. The data was structured into JSON and CSV formats
4. An interactive map was created using the Google Maps JavaScript API

## Data Structure

Each High Cross entry contains:
- **name**: The name of the High Cross
- **county**: The county in Ireland where it's located
- **coordinates**: Latitude and longitude coordinates

## Usage

### Viewing the Map

Open `high_crosses_map.html` in a web browser to view the interactive map. Click on any marker to see details about that High Cross.

### Using the Data

The data is available in both JSON and CSV formats for easy integration with various applications:

- `high_crosses.json` - Use for web applications or any JSON-compatible system
- `high_crosses.csv` - Import into Excel, Google Sheets, or other data analysis tools

## Sources

- High Cross information: [Megalithic Ireland](https://www.megalithicireland.com/High%20Cross%20Home.htm)
- Geocoding: Google Maps Geocoding API
- Reference map: [Google Maps](https://www.google.com/maps/d/u/0/viewer?ie=YTF8&hl=en&msa=0&11=53.251555%2C-6.147537&spn=0.464229%2C1.216736&z=7&om=1&mid=1hrpyZydez82vsjKUNv-HTIMVDzQ&ll=53.808415185477614%2C-7.750974000000006)

## License

This dataset is provided for educational and research purposes.