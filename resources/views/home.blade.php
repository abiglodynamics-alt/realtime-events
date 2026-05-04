@extends('layouts.app')

@section('title', 'EventHub - Discover Amazing Events')

@section('content')
<!-- Hero Section -->
<section class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-5xl font-bold mb-6">Create Memorable Events</h1>
            <p class="text-xl mb-8 max-w-3xl mx-auto">
                EventHub is your all-in-one platform for creating, managing, and hosting 
                incredible events with live interaction, Q&A, polls, and more.
            </p>
            <div class="flex justify-center space-x-4">
                <button class="bg-white text-indigo-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
                    Get Started Free
                </button>
                <button class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-indigo-600 transition">
                    Watch Demo
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Everything You Need</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">
                Powerful features to make your event planning seamless and engaging
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="p-6 bg-gray-50 rounded-xl">
                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                    <i data-lucide="calendar" class="h-6 w-6 text-indigo-600"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Event Management</h3>
                <p class="text-gray-600">
                    Create events with custom branding, multiple sessions, tracks, and days. 
                    Manage everything from one dashboard.
                </p>
            </div>
            <div class="p-6 bg-gray-50 rounded-xl">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                    <i data-lucide="users" class="h-6 w-6 text-green-600"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Speaker Profiles</h3>
                <p class="text-gray-600">
                    Showcase your speakers with beautiful profiles, bios, social links, 
                    and session assignments.
                </p>
            </div>
            <div class="p-6 bg-gray-50 rounded-xl">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                    <i data-lucide="message-circle" class="h-6 w-6 text-purple-600"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Live Interaction</h3>
                <p class="text-gray-600">
                    Engage attendees with real-time Q&A, live polls, notifications, 
                    and interactive sessions.
                </p>
            </div>
            <div class="p-6 bg-gray-50 rounded-xl">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-4">
                    <i data-lucide="qr-code" class="h-6 w-6 text-red-600"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">QR Check-in</h3>
                <p class="text-gray-600">
                    Fast and secure attendee check-in with automatically generated 
                    QR codes for each registration.
                </p>
            </div>
            <div class="p-6 bg-gray-50 rounded-xl">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                    <i data-lucide="clock" class="h-6 w-6 text-blue-600"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Agenda Builder</h3>
                <p class="text-gray-600">
                    Build detailed agendas with sessions across multiple days, 
                    tracks, and time slots with ease.
                </p>
            </div>
            <div class="p-6 bg-gray-50 rounded-xl">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mb-4">
                    <i data-lucide="bar-chart" class="h-6 w-6 text-yellow-600"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Analytics</h3>
                <p class="text-gray-600">
                    Track attendance, engagement metrics, and gather insights 
                    to improve future events.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Featured Events Section -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-12">
            <div>
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Featured Events</h2>
                <p class="text-gray-600">Discover upcoming events you might be interested in</p>
            </div>
            <a href="/events" class="text-indigo-600 hover:text-indigo-700 font-semibold flex items-center">
                View All Events
                <i data-lucide="arrow-right" class="h-4 w-4 ml-2"></i>
            </a>
        </div>
        
        <!-- Sample Events Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Event Card 1 -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition">
                <div class="h-48 bg-gradient-to-br from-blue-400 to-indigo-500"></div>
                <div class="p-6">
                    <div class="flex items-center text-sm text-gray-500 mb-2">
                        <i data-lucide="calendar" class="h-4 w-4 mr-1"></i>
                        <span>Dec 15-17, 2024</span>
                        <span class="mx-2">•</span>
                        <i data-lucide="map-pin" class="h-4 w-4 mr-1"></i>
                        <span>San Francisco, CA</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Tech Summit 2024</h3>
                    <p class="text-gray-600 mb-4">
                        Join industry leaders for three days of innovation, networking, 
                        and learning about the latest tech trends.
                    </p>
                    <div class="flex items-center justify-between">
                        <div class="flex -space-x-2">
                            <div class="w-8 h-8 rounded-full bg-gray-300 border-2 border-white"></div>
                            <div class="w-8 h-8 rounded-full bg-gray-400 border-2 border-white"></div>
                            <div class="w-8 h-8 rounded-full bg-gray-500 border-2 border-white"></div>
                            <span class="text-sm text-gray-500 ml-2">+24 speakers</span>
                        </div>
                        <span class="text-indigo-600 font-semibold">View Details</span>
                    </div>
                </div>
            </div>

            <!-- Event Card 2 -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition">
                <div class="h-48 bg-gradient-to-br from-green-400 to-teal-500"></div>
                <div class="p-6">
                    <div class="flex items-center text-sm text-gray-500 mb-2">
                        <i data-lucide="calendar" class="h-4 w-4 mr-1"></i>
                        <span>Jan 20-22, 2025</span>
                        <span class="mx-2">•</span>
                        <i data-lucide="map-pin" class="h-4 w-4 mr-1"></i>
                        <span>New York, NY</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Marketing World Conference</h3>
                    <p class="text-gray-600 mb-4">
                        The ultimate gathering for marketing professionals featuring 
                        workshops, keynotes, and networking opportunities.
                    </p>
                    <div class="flex items-center justify-between">
                        <div class="flex -space-x-2">
                            <div class="w-8 h-8 rounded-full bg-gray-300 border-2 border-white"></div>
                            <div class="w-8 h-8 rounded-full bg-gray-400 border-2 border-white"></div>
                            <span class="text-sm text-gray-500 ml-2">+18 speakers</span>
                        </div>
                        <span class="text-indigo-600 font-semibold">View Details</span>
                    </div>
                </div>
            </div>

            <!-- Event Card 3 -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition">
                <div class="h-48 bg-gradient-to-br from-orange-400 to-red-500"></div>
                <div class="p-6">
                    <div class="flex items-center text-sm text-gray-500 mb-2">
                        <i data-lucide="calendar" class="h-4 w-4 mr-1"></i>
                        <span>Feb 10-12, 2025</span>
                        <span class="mx-2">•</span>
                        <i data-lucide="map-pin" class="h-4 w-4 mr-1"></i>
                        <span>Austin, TX</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Startup Launch Festival</h3>
                    <p class="text-gray-600 mb-4">
                        Connect with entrepreneurs, investors, and innovators at 
                        the premier startup event in the Southwest.
                    </p>
                    <div class="flex items-center justify-between">
                        <div class="flex -space-x-2">
                            <div class="w-8 h-8 rounded-full bg-gray-300 border-2 border-white"></div>
                            <div class="w-8 h-8 rounded-full bg-gray-400 border-2 border-white"></div>
                            <div class="w-8 h-8 rounded-full bg-gray-500 border-2 border-white"></div>
                            <span class="text-sm text-gray-500 ml-2">+32 speakers</span>
                        </div>
                        <span class="text-indigo-600 font-semibold">View Details</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 bg-indigo-600">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold text-white mb-4">Ready to Host Your Event?</h2>
        <p class="text-indigo-100 mb-8 max-w-2xl mx-auto">
            Join thousands of organizers who trust EventHub to power their events. 
            Start creating your event today.
        </p>
        <button class="bg-white text-indigo-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
            Create Your First Event
        </button>
    </div>
</section>
@endsection
