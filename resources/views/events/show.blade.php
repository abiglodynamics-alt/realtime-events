@extends('layouts.app')

@section('title', $event->name . ' - EventHub')

@push('scripts')
<script>
    // Enable Pusher for real-time updates
    const pusher = new Pusher('{{ config("broadcasting.connections.pusher.key") }}', {
        cluster: '{{ config("broadcasting.connections.pusher.options.cluster") }}',
        encrypted: true
    });

    const channel = pusher.subscribe('event.{{ $event->id }}');
    
    channel.bind('session.updated', function(data) {
        console.log('Session updated:', data);
        // Refresh session data or show notification
    });

    channel.bind('new-question', function(data) {
        console.log('New question:', data);
        // Show notification for new Q&A question
    });

    channel.bind('poll-created', function(data) {
        console.log('New poll:', data);
        // Show notification for new poll
    });
</script>
@endpush

@section('content')
<!-- Event Header -->
<div class="relative">
    @if($event->image_url)
    <div class="h-64 bg-cover bg-center" style="background-image: url('{{ $event->image_url }}')">
        <div class="absolute inset-0 bg-gradient-to-b from-black/50 to-black/80"></div>
    </div>
    @else
    <div class="h-64 bg-gradient-to-r from-indigo-600 to-purple-600 relative">
        <div class="absolute inset-0 bg-gradient-to-b from-black/30 to-black/70"></div>
    </div>
    @endif
    
    <div class="absolute bottom-0 left-0 right-0 p-8 text-white">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center space-x-2 mb-2">
                <span class="bg-indigo-500 px-3 py-1 rounded-full text-sm font-semibold">
                    {{ ucfirst($event->status) }}
                </span>
                @if($event->is_published)
                <span class="bg-green-500 px-3 py-1 rounded-full text-sm font-semibold">
                    Published
                </span>
                @endif
            </div>
            <h1 class="text-4xl font-bold mb-2">{{ $event->name }}</h1>
            <div class="flex items-center space-x-6 text-lg">
                <div class="flex items-center">
                    <i data-lucide="calendar" class="h-5 w-5 mr-2"></i>
                    <span>{{ $event->start_date->format('M d, Y') }} - {{ $event->end_date->format('M d, Y') }}</span>
                </div>
                <div class="flex items-center">
                    <i data-lucide="clock" class="h-5 w-5 mr-2"></i>
                    <span>{{ $event->timezone }}</span>
                </div>
                <div class="flex items-center">
                    <i data-lucide="map-pin" class="h-5 w-5 mr-2"></i>
                    <span>{{ $event->location_name ?? 'Online' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Event Navigation Tabs -->
<div class="bg-white shadow-sm sticky top-16 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex space-x-8 overflow-x-auto">
            <a href="#overview" class="py-4 border-b-2 border-indigo-600 text-indigo-600 font-semibold whitespace-nowrap">
                Overview
            </a>
            <a href="#agenda" class="py-4 border-b-2 border-transparent text-gray-600 hover:text-gray-900 font-semibold whitespace-nowrap">
                Agenda
            </a>
            <a href="#speakers" class="py-4 border-b-2 border-transparent text-gray-600 hover:text-gray-900 font-semibold whitespace-nowrap">
                Speakers
            </a>
            <a href="#qa" class="py-4 border-b-2 border-transparent text-gray-600 hover:text-gray-900 font-semibold whitespace-nowrap">
                Q&A
            </a>
            <a href="#polls" class="py-4 border-b-2 border-transparent text-gray-600 hover:text-gray-900 font-semibold whitespace-nowrap">
                Polls
            </a>
            <a href="#networking" class="py-4 border-b-2 border-transparent text-gray-600 hover:text-gray-900 font-semibold whitespace-nowrap">
                Networking
            </a>
        </div>
    </div>
</div>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Overview Section -->
    <section id="overview" class="mb-16">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">About This Event</h2>
                <div class="prose max-w-none">
                    <p class="text-gray-600 leading-relaxed">{{ $event->description }}</p>
                </div>
                
                @if($event->days()->exists())
                <div class="mt-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Event Days</h3>
                    <div class="space-y-4">
                        @foreach($event->days as $day)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-semibold text-gray-900">{{ $day->name }}</h4>
                                    <p class="text-sm text-gray-600">{{ $day->date->format('l, F j, Y') }}</p>
                                </div>
                                <span class="text-indigo-600 font-semibold">
                                    {{ $day->sessions()->count() }} Sessions
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            
            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-md p-6 sticky top-40">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Event Details</h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <i data-lucide="calendar" class="h-5 w-5 text-gray-400 mr-3 mt-0.5"></i>
                            <div>
                                <p class="text-sm text-gray-600">Date</p>
                                <p class="font-semibold text-gray-900">
                                    {{ $event->start_date->format('M d') }} - {{ $event->end_date->format('M d, Y') }}
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <i data-lucide="clock" class="h-5 w-5 text-gray-400 mr-3 mt-0.5"></i>
                            <div>
                                <p class="text-sm text-gray-600">Timezone</p>
                                <p class="font-semibold text-gray-900">{{ $event->timezone }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <i data-lucide="map-pin" class="h-5 w-5 text-gray-400 mr-3 mt-0.5"></i>
                            <div>
                                <p class="text-sm text-gray-600">Location</p>
                                <p class="font-semibold text-gray-900">{{ $event->location_name ?? 'Virtual Event' }}</p>
                                @if($event->location_address)
                                <p class="text-sm text-gray-600">{{ $event->location_address }}</p>
                                @endif
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <i data-lucide="users" class="h-5 w-5 text-gray-400 mr-3 mt-0.5"></i>
                            <div>
                                <p class="text-sm text-gray-600">Attendees</p>
                                <p class="font-semibold text-gray-900">
                                    {{ $event->attendees()->where('rsvp_status', 'confirmed')->count() }} Confirmed
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    @auth
                    <button class="w-full mt-6 bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 transition">
                        Register Now
                    </button>
                    @else
                    <a href="/login" class="block w-full mt-6 bg-indigo-600 text-white text-center py-3 rounded-lg font-semibold hover:bg-indigo-700 transition">
                        Sign In to Register
                    </a>
                    @endauth
                    
                    <!-- Organizer Info -->
                    <div class="mt-6 pt-6 border-t">
                        <p class="text-sm text-gray-600 mb-2">Organized by</p>
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                <i data-lucide="user" class="h-5 w-5 text-indigo-600"></i>
                            </div>
                            <div class="ml-3">
                                <p class="font-semibold text-gray-900">{{ $event->organizer->name }}</p>
                                <p class="text-sm text-gray-600">Event Organizer</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Agenda Section -->
    <section id="agenda" class="mb-16 scroll-mt-32">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Agenda</h2>
        
        @if($event->sessions()->exists())
        <div class="space-y-6">
            @foreach($event->days as $day)
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="bg-indigo-600 text-white px-6 py-4">
                    <h3 class="text-xl font-bold">{{ $day->name }}</h3>
                    <p class="text-indigo-100">{{ $day->date->format('l, F j, Y') }}</p>
                </div>
                <div class="divide-y">
                    @foreach($day->sessions()->with(['speakers', 'track'])->orderBy('start_time')->get() as $session)
                    <div class="p-6 hover:bg-gray-50 transition">
                        <div class="flex items-start">
                            <div class="w-24 flex-shrink-0">
                                <p class="text-sm font-semibold text-indigo-600">
                                    {{ $session->start_time->format('g:i A') }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $session->end_time->format('g:i A') }}
                                </p>
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="text-lg font-bold text-gray-900">{{ $session->title }}</h4>
                                    @if($session->track)
                                    <span class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-xs font-semibold">
                                        {{ $session->track->name }}
                                    </span>
                                    @endif
                                </div>
                                <p class="text-gray-600 mb-3">{{ $session->description }}</p>
                                <div class="flex items-center space-x-4">
                                    <div class="flex items-center">
                                        <i data-lucide="type" class="h-4 w-4 text-gray-400 mr-1"></i>
                                        <span class="text-sm text-gray-600">{{ ucfirst($session->type) }}</span>
                                    </div>
                                    @if($session->location_name)
                                    <div class="flex items-center">
                                        <i data-lucide="map-pin" class="h-4 w-4 text-gray-400 mr-1"></i>
                                        <span class="text-sm text-gray-600">{{ $session->location_name }}</span>
                                    </div>
                                    @endif
                                </div>
                                
                                @if($session->speakers->count() > 0)
                                <div class="mt-4 flex items-center space-x-3">
                                    @foreach($session->speakers as $speaker)
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-full bg-gray-300"></div>
                                        <span class="ml-2 text-sm text-gray-700">{{ $speaker->name }}</span>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-12 bg-gray-50 rounded-xl">
            <i data-lucide="calendar" class="h-12 w-12 text-gray-400 mx-auto mb-4"></i>
            <p class="text-gray-600">Agenda will be announced soon</p>
        </div>
        @endif
    </section>

    <!-- Speakers Section -->
    <section id="speakers" class="mb-16 scroll-mt-32">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Speakers</h2>
        
        @if($speakers->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($speakers as $speaker)
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
                <div class="flex items-start mb-4">
                    <div class="w-16 h-16 rounded-full bg-gray-300 flex-shrink-0"></div>
                    <div class="ml-4">
                        <h3 class="text-lg font-bold text-gray-900">{{ $speaker->name }}</h3>
                        @if($speaker->job_title || $speaker->company)
                        <p class="text-gray-600 text-sm">
                            {{ $speaker->job_title }}
                            @if($speaker->company) at {{ $speaker->company }} @endif
                        </p>
                        @endif
                    </div>
                </div>
                <p class="text-gray-600 text-sm mb-4 line-clamp-3">{{ $speaker->bio }}</p>
                
                @if($speaker->social_links)
                <div class="flex space-x-3">
                    @if($speaker->social_links['twitter'] ?? null)
                    <a href="{{ $speaker->social_links['twitter'] }}" class="text-gray-400 hover:text-blue-400">
                        <i data-lucide="twitter" class="h-5 w-5"></i>
                    </a>
                    @endif
                    @if($speaker->social_links['linkedin'] ?? null)
                    <a href="{{ $speaker->social_links['linkedin'] }}" class="text-gray-400 hover:text-blue-600">
                        <i data-lucide="linkedin" class="h-5 w-5"></i>
                    </a>
                    @endif
                    @if($speaker->social_links['github'] ?? null)
                    <a href="{{ $speaker->social_links['github'] }}" class="text-gray-400 hover:text-gray-900">
                        <i data-lucide="github" class="h-5 w-5"></i>
                    </a>
                    @endif
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-12 bg-gray-50 rounded-xl">
            <i data-lucide="users" class="h-12 w-12 text-gray-400 mx-auto mb-4"></i>
            <p class="text-gray-600">Speaker lineup will be announced soon</p>
        </div>
        @endif
    </section>

    <!-- Q&A Section -->
    <section id="qa" class="mb-16 scroll-mt-32">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Live Q&A</h2>
        
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Ask a Question</h3>
            <form id="questionForm" class="space-y-4">
                @csrf
                <textarea 
                    name="question" 
                    rows="3" 
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="Type your question here..."
                    required
                ></textarea>
                <div class="flex justify-end">
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-indigo-700 transition">
                        Submit Question
                    </button>
                </div>
            </form>
        </div>
        
        <div id="questionsList" class="space-y-4">
            <!-- Questions will be loaded here via JavaScript -->
            <div class="text-center py-8 bg-gray-50 rounded-xl">
                <p class="text-gray-600">No questions yet. Be the first to ask!</p>
            </div>
        </div>
    </section>

    <!-- Polls Section -->
    <section id="polls" class="mb-16 scroll-mt-32">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Live Polls</h2>
        
        <div id="pollsList" class="space-y-6">
            <!-- Polls will be loaded here via JavaScript -->
            <div class="text-center py-12 bg-gray-50 rounded-xl">
                <i data-lucide="bar-chart" class="h-12 w-12 text-gray-400 mx-auto mb-4"></i>
                <p class="text-gray-600">No active polls at the moment</p>
            </div>
        </div>
    </section>
</main>

<script>
// Question submission
document.getElementById('questionForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await axios.post('/api/events/{{ $event->id }}/questions', {
            question: formData.get('question')
        });
        
        if (response.data.success) {
            this.reset();
            alert('Question submitted successfully!');
        }
    } catch (error) {
        console.error('Error submitting question:', error);
        alert('Failed to submit question. Please try again.');
    }
});

// Load questions
async function loadQuestions() {
    try {
        const response = await axios.get('/api/events/{{ $event->id }}/questions');
        const questions = response.data;
        
        const container = document.getElementById('questionsList');
        if (questions.length === 0) {
            container.innerHTML = `
                <div class="text-center py-8 bg-gray-50 rounded-xl">
                    <p class="text-gray-600">No questions yet. Be the first to ask!</p>
                </div>
            `;
            return;
        }
        
        container.innerHTML = questions.map(q => `
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-gray-900 font-medium">${q.question}</p>
                        ${q.is_answered ? '<span class="text-green-600 text-sm mt-2">✓ Answered</span>' : ''}
                    </div>
                    <div class="flex items-center space-x-4">
                        <button class="flex items-center text-gray-600 hover:text-indigo-600">
                            <i data-lucide="thumbs-up" class="h-5 w-5 mr-1"></i>
                            <span>${q.votes || 0}</span>
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
        
        lucide.createIcons();
    } catch (error) {
        console.error('Error loading questions:', error);
    }
}

// Load polls
async function loadPolls() {
    try {
        const response = await axios.get('/api/events/{{ $event->id }}/polls');
        const polls = response.data;
        
        const container = document.getElementById('pollsList');
        if (polls.length === 0) {
            container.innerHTML = `
                <div class="text-center py-12 bg-gray-50 rounded-xl">
                    <i data-lucide="bar-chart" class="h-12 w-12 text-gray-400 mx-auto mb-4"></i>
                    <p class="text-gray-600">No active polls at the moment</p>
                </div>
            `;
            return;
        }
        
        container.innerHTML = polls.map(poll => `
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">${poll.question}</h3>
                <div class="space-y-3">
                    ${poll.options.map(option => `
                        <button class="w-full text-left px-4 py-3 border border-gray-300 rounded-lg hover:border-indigo-600 hover:bg-indigo-50 transition">
                            ${option.text}
                        </button>
                    `).join('')}
                </div>
            </div>
        `).join('');
        
        lucide.createIcons();
    } catch (error) {
        console.error('Error loading polls:', error);
    }
}

// Initial load
loadQuestions();
loadPolls();

// Refresh every 30 seconds
setInterval(loadQuestions, 30000);
setInterval(loadPolls, 30000);
</script>
@endsection
