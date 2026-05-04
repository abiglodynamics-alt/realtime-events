# Event Management Platform

A comprehensive event management platform similar to Eventee, built with Laravel. This platform provides features for event organization, agenda management, speaker profiles, attendee registration with QR code check-in, and live interaction capabilities including Q&A and polls.

## Features

### 1. Events
- Title, description, image, location, timezone
- Start/end date management
- Organizer assignment (user/team)
- Published/draft status
- Unique slug generation

### 2. Agenda & Sessions
- Sessions organized under events
- Track-based organization
- Day-based scheduling
- Speaker assignments
- Session types (keynote, talk, workshop, panel, break, other)
- Live streaming support
- Recording URLs

### 3. Speakers
- Profile image and bio
- Social media links (Twitter, LinkedIn, GitHub, Website)
- Job title and company
- Many-to-many relationship with sessions
- Moderator designation

### 4. Attendees
- Extended user model
- RSVP status management (pending, confirmed, cancelled, waitlist)
- QR code generation for check-in
- Check-in tracking
- Ticket type support

### 5. Live Interaction
- Real-time broadcasting via Laravel Echo/WebSockets
- Q&A system with upvoting
- Live polls with multiple choice support
- Real-time notifications

## Database Schema

The platform includes the following tables:

- `users` - User accounts with profile information
- `events` - Event details and metadata
- `tracks` - Event tracks for session organization
- `event_days` - Multi-day event support
- `sessions` - Individual sessions/talks
- `speakers` - Speaker profiles
- `session_speaker` - Pivot table for session-speaker relationships
- `attendees` - Event registrations
- `polls` - Interactive polls
- `poll_options` - Poll answer options
- `poll_votes` - Poll vote records
- `questions` - Q&A questions
- `notifications` - System notifications

## API Endpoints

### Public Endpoints
- `GET /api/events` - List published upcoming events
- `GET /api/events/{event}` - Get event details

### Protected Endpoints (Authentication Required)

#### Event Management
- `POST /api/events` - Create new event
- `PUT /api/events/{event}` - Update event
- `DELETE /api/events/{event}` - Delete event

#### Attendee/RSVP
- `POST /api/events/{event}/rsvp` - Register for an event
- `GET /api/my-events` - Get user's registered events
- `GET /api/attendees/{attendee}/qr-code` - Download QR code
- `POST /api/attendees/{attendee}/cancel` - Cancel registration
- `POST /api/check-in` - Check in attendee (organizer only)
- `POST /api/attendees/{attendee}/confirm` - Confirm registration (organizer only)

#### Q&A
- `POST /api/questions` - Ask a question
- `GET /api/questions` - Get questions for event/session
- `GET /api/questions/unanswered` - Get unanswered questions
- `POST /api/questions/{question}/upvote` - Upvote a question
- `POST /api/questions/{question}/answer` - Answer a question
- `POST /api/questions/{question}/toggle-visibility` - Hide/show question

#### Polls
- `POST /api/polls` - Create a poll
- `GET /api/polls/active` - Get active polls
- `GET /api/polls/{poll}` - Get poll results
- `POST /api/polls/{poll}/vote` - Vote on a poll
- `POST /api/polls/{poll}/toggle-active` - Activate/deactivate poll
- `POST /api/polls/{poll}/toggle-results` - Toggle results visibility

## Broadcasting Events

The platform broadcasts the following real-time events:

- `SessionStarted` - Notifies when a session begins
- `QuestionAsked` - Broadcasts new questions to the channel
- `PollResultsUpdated` - Updates poll results in real-time

Channels:
- `event.{event_id}` - Public channel for event updates
- `session.{session_id}` - Private channel for session-specific updates

## Installation

1. Clone the repository
2. Install dependencies: `composer install`
3. Copy `.env.example` to `.env` and configure database
4. Run migrations: `php artisan migrate`
5. Set up broadcasting driver (Pusher or Laravel WebSockets)
6. Generate application key: `php artisan key:generate`

## Configuration

### Broadcasting
Configure `config/broadcasting.php` to use either:
- Pusher (recommended for production)
- Laravel WebSockets (self-hosted alternative)

### Queue
Configure queue driver in `.env`:
```
QUEUE_CONNECTION=redis
```

### Storage
For QR code storage, ensure proper disk configuration:
```
FILESYSTEM_DISK=public
```

## Model Relationships

```
User
├── hasMany → Event (as organizer)
├── hasOne → Speaker
└── hasMany → Attendee

Event
├── belongsTo → User (organizer)
├── hasMany → Track
├── hasMany → EventDay
├── hasMany → Session
├── hasMany → Attendee
├── hasMany → Poll
└── hasMany → Question

Session
├── belongsTo → Event
├── belongsTo → Track
├── belongsTo → EventDay
├── belongsToMany → Speaker
├── hasMany → Question
└── hasMany → Poll

Speaker
├── belongsTo → User
└── belongsToMany → Session

Attendee
├── belongsTo → User
├── belongsTo → Event
├── hasMany → Question
└── hasMany → PollVote

Poll
├── belongsTo → Event
├── belongsTo → Session
├── hasMany → PollOption
└── hasMany → PollVote

PollOption
├── belongsTo → Poll
└── hasMany → PollVote

Question
├── belongsTo → Session
├── belongsTo → Event
├── belongsTo → Attendee
└── belongsTo → User (answerer)
```

## License

MIT License
