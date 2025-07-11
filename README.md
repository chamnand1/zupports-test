# 🍽️ Restaurant Search App (Laravel + Vue + Inertia)

This project is a restaurant search web app built with Laravel backend and Vue 3 frontend using Inertia.js. It integrates Google Maps Geocode and Places APIs to fetch restaurants based on a keyword. The backend caches results with a configurable TTL to improve performance. The frontend features a responsive UI with Tailwind CSS and TypeScript.

## Setup & Installation

Clone the repository, install dependencies, configure environment variables, and build assets.

### Step 1: Clone and install dependencies

**Clone the repo:**
```bash
git clone https://github.com/chamnand1/zupports-test.git
cd zupports-test
```

**Install PHP dependencies:**
```bash
composer install
```

**Install Node.js dependencies:**
```bash
npm install
```

### Step 2: Configure Environment and Google Maps API Key

**Copy `.env.example` to `.env`:**
```bash
cp .env.example .env
```

- Obtain your **Google Maps API Key** (see instructions below)
- Add your API key in `.env`:
```env
GOOGLE_MAPS_API_KEY=your_google_maps_api_key_here
```

### How to get a Google Maps API Key

1. Go to [Google Cloud Console](https://console.cloud.google.com/).
2. Create a new project or select an existing one.
3. Navigate to **APIs & Services > Credentials**.
4. Click **Create Credentials** and select **API key**.
5. Copy the generated API key.
6. Enable the following APIs under **APIs & Services > Library**:
   - Maps JavaScript API
   - Geocoding API
   - Places API
7. (Optional but recommended) Restrict the API key usage for security:
   - Limit to required APIs only.
   - Set HTTP referrers (your domain) if applicable.

### Step 3: Build frontend assets

```bash
npm run build
```

## Development

**Start Laravel server:**
```bash
composer run dev
```

Access the app at [http://localhost:8000](http://localhost:8000).

### API Endpoints

**Search restaurants:**
```
GET /restaurants/search?keyword=Asoke
```

**Example:**
```
http://localhost:8000/restaurants/search?keyword=Asoke
```

## Features

- Search restaurants by keyword (default: "Bang Sue")
- Use Google Maps APIs for geocoding and places
- Cache search results with TTL for performance
- Vue 3 + TypeScript frontend with Inertia.js SPA-like UX
- Responsive UI styled with Tailwind CSS

## Project Structure

- `app/Services/RestaurantService.php`: Google Maps API service logic
- `app/Http/Controllers/Restaurants/RestaurantController.php`: Search controller with caching
- `resources/js/pages/Restaurants.vue`: Vue page component
- `resources/js/components/RestaurantList.vue`: List display component
- Config keys in `config/services.php` under `google_maps`

## Testing

Run tests:
```bash
php artisan test
```

## Notes

- Default keyword configurable; overridden via query string