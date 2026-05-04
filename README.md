# EventHub - Event Management Platform

A comprehensive event management platform similar to Eventee, built with Laravel. Features include event creation, agenda management, speaker profiles, attendee registration with QR codes, and real-time interaction through Q&A and polls.

## Features

### 1. Events
- Title, description, image, location, timezone
- Start/end date management
- Organizer assignment (user/team)
- Auto-generated unique slugs
- Published/draft status

### 2. Agenda & Sessions
- Sessions organized under events
- Day and track assignments
- Speaker assignments (many-to-many)
- Session types: keynote, talk, workshop, panel, break
- Live streaming URL support

### 3. Speakers
- Profile images and bios
- Social links (Twitter, LinkedIn, GitHub, website)
- Job titles and companies
- Multiple session assignments

### 4. Attendees
- Extended user model
- RSVP status management (pending/confirmed/cancelled/waitlist)
- QR code generation for check-in
- Ticket types and special requirements

### 5. Live Interaction
- Real-time broadcasting via Laravel Echo/Pusher
- Q&A system with upvoting
- Live polls (single/multiple choice)
- Push notifications

## Quick Start

### Prerequisites
- PHP 8.1+
- Composer
- MySQL/PostgreSQL
- Node.js & NPM (optional, for assets)

### Installation

1. **Clone and install dependencies**
```bash
cd /workspace
composer install
```

2. **Configure environment**
```bash
cp .env.example .env
php artisan key:generate
```

3. **Update database configuration in `.env`**
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eventhub
DB_USERNAME=root
DB_PASSWORD=
```

4. **Run migrations**
```bash
php artisan migrate
```

5. **Seed sample data (optional)**
```bash
php artisan db:seed
```

6. **Start the development server**
```bash
php artisan serve
```

Visit `http://localhost:8000` to view the application.

## Available Pages

### Public Pages
- **Home** (`/`) - Landing page with featured events
- **Events List** (`/events`) - Browse all published events
- **Event Detail** (`/events/{slug}`) - Full event page with agenda, speakers, Q&A, polls
- **Speakers** (`/speakers`) - Speaker directory

### Key Features on Event Page
- **Overview Tab** - Event details, description, organizer info
- **Agenda Tab** - Sessions organized by day and track
- **Speakers Tab** - All speakers for the event
- **Q&A Tab** - Live question submission and voting
- **Polls Tab** - Active polls with real-time results

## API Endpoints

### Events
```
GET    /api/events              - List all events
POST   /api/events              - Create event
GET    /api/events/{id}         - Get event details
PUT    /api/events/{id}         - Update event
DELETE /api/events/{id}         - Delete event
```

### Sessions
```
GET    /api/events/{id}/sessions     - List sessions
POST   /api/events/{id}/sessions     - Create session
```

### Q&A
```
GET    /api/events/{id}/questions           - Get questions
POST   /api/events/{id}/questions           - Ask question
POST   /api/events/{id}/questions/{id}/vote - Vote on question
```

### Polls
```
GET    /api/events/{id}/polls          - Get active polls
POST   /api/events/{id}/polls          - Create poll
POST   /api/events/{id}/polls/{id}/vote - Vote on poll
```

### Attendees
```
POST   /api/events/{id}/attendees           - Register attendee
GET    /api/events/{id}/attendees/{id}/qr   - Get QR code
POST   /api/attendees/checkin               - Check-in attendee
```

## Real-Time Features

### Broadcasting Configuration

For real-time Q&A and polls, configure broadcasting in `.env`:

```
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=mt1
```

Or use Laravel WebSockets:
```bash
composer require beyondcode/laravel-websockets
php artisan vendor:publish --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider" --tag="migrations"
php artisan migrate
php artisan websockets:serve
```

## Database Schema

The application includes 13 main tables:
- `users` - User accounts
- `events` - Event information
- `event_days` - Multi-day event structure
- `tracks` - Parallel session tracks
- `sessions` - Individual sessions/talks
- `speakers` - Speaker profiles
- `session_speaker` - Session-speaker pivot
- `attendees` - Event registrations
- `questions` - Q&A questions
- `polls` - Live polls
- `poll_options` - Poll choices
- `poll_votes` - Poll votes
- `notifications` - Event notifications

## Frontend Views

Created Blade templates:
- `layouts/app.blade.php` - Main layout with navigation
- `home.blade.php` - Landing page
- `events/index.blade.php` - Events listing
- `events/show.blade.php` - Event detail page with tabs

## Customization

### Branding
Update colors in views by modifying Tailwind classes:
- Primary: `indigo-600`
- Secondary: `purple-600`

### Features
Enable/disable features in config or by commenting routes.

## Testing

```bash
php artisan test
```

## Security Notes

- Always validate and sanitize user input
- Use authorization checks for organizer actions
- Enable HTTPS in production
- Keep dependencies updated

## License

MIT License

---

**Preview the application:** Run `php artisan serve` and visit `http://localhost:8000`
