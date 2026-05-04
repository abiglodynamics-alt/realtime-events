# EventHub Platform

A comprehensive event management platform similar to Eventee, built with Laravel. Manage events, agendas, speakers, attendees, and enable live interactions with Q&A and polls.

## Features

### 🎯 Core Features
- **Event Management** - Create, update, and manage events with full details
- **Agenda & Sessions** - Organize sessions by days and tracks
- **Speaker Management** - Profile pages with social links
- **Attendee Registration** - RSVP system with QR code check-in
- **Live Interaction** - Real-time Q&A and polls using WebSockets

### 📋 Event Features
- Title, description, images (banner & thumbnail)
- Location details (venue name, address, city, country)
- Timezone support
- Start/end dates and times
- Registration periods with deadlines
- Maximum attendee capacity
- Published/draft status
- Featured events

### 📅 Agenda Features
- Multi-day event support
- Track-based session organization
- Session types: keynote, talk, workshop, panel, break, networking
- Room assignments
- Live streaming URLs
- Recording and slides links
- Speaker assignments

### 👥 Speaker Features
- Profile images and bios
- Job titles and companies
- Social media links (Twitter, LinkedIn, GitHub, website)
- Session assignments
- Featured speakers

### 🎫 Attendee Features
- User registration and profiles
- RSVP with multiple ticket types (Free, VIP, Premium, Student, Speaker, Sponsor, Staff)
- Status management (Pending, Confirmed, Cancelled, Waitlist)
- Automatic QR code generation
- Check-in tracking
- Dietary requirements and T-shirt sizes

### 💬 Live Interaction
- **Q&A System**
  - Ask questions during events/sessions
  - Upvote questions (one per user)
  - Mark as answered
  - Hide inappropriate questions
  - Real-time updates via WebSockets

- **Live Polls**
  - Single or multiple choice polls
  - Activate/deactivate polls
  - Configurable result visibility (never, after vote, always)
  - Real-time vote counting
  - Percentage calculations
  - Duplicate vote prevention

## Installation

### Prerequisites
- PHP 8.2+
- Composer
- MySQL 8.0+ or PostgreSQL
- Node.js & NPM (for frontend assets)
- Redis (optional, for queues and caching)

### Setup Steps

1. **Clone the repository**
```bash
git clone <repository-url>
cd eventhub-platform
```

2. **Install dependencies**
```bash
composer install
npm install
```

3. **Configure environment**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Update database configuration in `.env`**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eventhub
DB_USERNAME=root
DB_PASSWORD=secret
```

5. **Run migrations**
```bash
php artisan migrate
```

6. **Configure broadcasting (for real-time features)**
```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1
```

7. **Start the development server**
```bash
php artisan serve
```

8. **For real-time features, run WebSocket server**
```bash
php artisan websockets:serve
```

## Project Structure

```
eventhub-platform/
├── src/
│   ├── Models/              # Eloquent models
│   │   ├── Event.php
│   │   ├── EventDay.php
│   │   ├── Track.php
│   │   ├── Session.php
│   │   ├── Speaker.php
│   │   ├── Attendee.php
│   │   ├── Question.php
│   │   ├── QuestionVote.php
│   │   ├── Poll.php
│   │   ├── PollOption.php
│   │   ├── PollVote.php
│   │   └── User.php
│   ├── Controllers/         # API controllers
│   │   ├── EventController.php
│   │   ├── AgendaController.php
│   │   ├── QuestionController.php
│   │   └── PollController.php
│   └── Broadcasts/          # Broadcasting events
│       ├── QuestionCreated.php
│       ├── PollUpdated.php
│       └── EventStarted.php
├── database/
│   └── schema.sql           # Database schema
├── routes/
│   └── api.php              # API routes
├── resources/
│   └── views/               # Blade templates
└── composer.json
```

## API Endpoints

### Events
- `GET /api/events` - List all published events
- `GET /api/events/{slug}` - Get event details
- `POST /api/events` - Create event (auth required)
- `PUT /api/events/{event}` - Update event (organizer only)
- `DELETE /api/events/{event}` - Delete event (organizer only)
- `POST /api/events/{event}/rsvp` - Register for event
- `DELETE /api/events/{event}/rsvp` - Cancel registration
- `GET /api/events/{event}/attendees` - List attendees (organizer only)
- `POST /api/events/{event}/check-in` - Check in attendee (organizer only)

### Agenda/Sessions
- `GET /api/events/{event}/agenda` - List all sessions
- `GET /api/events/{event}/agenda/{session}` - Get session details
- `POST /api/events/{event}/agenda` - Create session (organizer only)
- `PUT /api/events/{event}/agenda/{session}` - Update session (organizer only)
- `DELETE /api/events/{event}/agenda/{session}` - Delete session (organizer only)
- `POST /api/events/{event}/agenda/{session}/speakers` - Add speakers (organizer only)
- `DELETE /api/events/{event}/agenda/{session}/speakers/{speaker}` - Remove speaker (organizer only)

### Q&A
- `GET /api/events/{event}/questions` - List questions
- `POST /api/events/{event}/questions` - Ask question
- `POST /api/events/{event}/questions/{question}/upvote` - Upvote question
- `DELETE /api/events/{event}/questions/{question}/upvote` - Remove upvote
- `POST /api/events/{event}/questions/{question}/answer` - Answer question (organizer only)
- `POST /api/events/{event}/questions/{question}/hide` - Hide question (organizer only)
- `POST /api/events/{event}/questions/{question}/show` - Show question (organizer only)
- `DELETE /api/events/{event}/questions/{question}` - Delete question (organizer only)

### Polls
- `GET /api/events/{event}/polls` - List polls
- `GET /api/events/{event}/polls/{poll}` - Get poll details
- `POST /api/events/{event}/polls` - Create poll (organizer only)
- `PUT /api/events/{event}/polls/{poll}` - Update poll (organizer only)
- `POST /api/events/{event}/polls/{poll}/activate` - Activate poll (organizer only)
- `POST /api/events/{event}/polls/{poll}/deactivate` - Deactivate poll (organizer only)
- `POST /api/events/{event}/polls/{poll}/vote` - Vote on poll
- `DELETE /api/events/{event}/polls/{poll}` - Delete poll (organizer only)

## Real-Time Events

### Client-side Subscription
```javascript
// Subscribe to event channels
Echo.channel(`events.${eventId}.questions`)
    .listen('QuestionCreated', (e) => {
        console.log('New question:', e.question);
    });

Echo.channel(`events.${eventId}.polls`)
    .listen('PollUpdated', (e) => {
        console.log('Poll updated:', e.poll);
    });
```

## Configuration

### Queue Configuration
For better performance, configure queues for:
- Email notifications
- QR code generation
- Broadcast events

```env
QUEUE_CONNECTION=redis
```

### Storage Configuration
For image uploads:
```env
FILESYSTEM_DISK=public
```

Run:
```bash
php artisan storage:link
```

## Testing

```bash
php artisan test
```

## Security Features

- Organizer authorization checks
- RSVP status validation
- Duplicate vote prevention
- Question moderation capabilities
- Secure QR code data

## License

MIT License

## Support

For issues and feature requests, please open an issue on GitHub.
