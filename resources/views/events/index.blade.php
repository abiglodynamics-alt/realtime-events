@extends('layouts.app')

@section('title', 'All Events - EventHub')

@section('content')
<div class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Discover Events</h1>
        <p class="text-gray-600 max-w-3xl">
            Browse upcoming events, conferences, workshops, and meetups. 
            Find something that interests you and register today.
        </p>
        
        <!-- Search and Filters -->
        <div class="mt-8 flex flex-col md:flex-row gap-4">
            <div class="flex-1 relative">
                <input 
                    type="text" 
                    placeholder="Search events..." 
                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                />
                <i data-lucide="search" class="h-5 w-5 text-gray-400 absolute left-3 top-3.5"></i>
            </div>
            <select class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <option>All Categories</option>
                <option>Technology</option>
                <option>Business</option>
                <option>Marketing</option>
                <option>Design</option>
                <option>Science</option>
            </select>
            <select class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <option>Any Location</option>
                <option>Online</option>
                <option>In-Person</option>
            </select>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Event Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <!-- Sample Event Card 1 -->
        <a href="/events/1" class="group bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition">
            <div class="h-48 bg-gradient-to-br from-blue-400 to-indigo-500 group-hover:scale-105 transition-transform duration-300"></div>
            <div class="p-6">
                <div class="flex items-center justify-between mb-3">
                    <span class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-xs font-semibold">
                        Conference
                    </span>
                    <span class="text-sm text-gray-500">Dec 15-17, 2024</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-indigo-600 transition">
                    Tech Summit 2024
                </h3>
                <p class="text-gray-600 mb-4 line-clamp-2">
                    Join industry leaders for three days of innovation, networking, 
                    and learning about the latest tech trends.
                </p>
                <div class="flex items-center text-sm text-gray-500 mb-4">
                    <i data-lucide="map-pin" class="h-4 w-4 mr-1"></i>
                    <span>San Francisco, CA</span>
                </div>
                <div class="flex items-center justify-between pt-4 border-t">
                    <div class="flex -space-x-2">
                        <div class="w-8 h-8 rounded-full bg-gray-300 border-2 border-white"></div>
                        <div class="w-8 h-8 rounded-full bg-gray-400 border-2 border-white"></div>
                        <div class="w-8 h-8 rounded-full bg-gray-500 border-2 border-white"></div>
                    </div>
                    <span class="text-indigo-600 font-semibold text-sm">View Details →</span>
                </div>
            </div>
        </a>

        <!-- Sample Event Card 2 -->
        <a href="/events/2" class="group bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition">
            <div class="h-48 bg-gradient-to-br from-green-400 to-teal-500 group-hover:scale-105 transition-transform duration-300"></div>
            <div class="p-6">
                <div class="flex items-center justify-between mb-3">
                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold">
                        Workshop
                    </span>
                    <span class="text-sm text-gray-500">Jan 20-22, 2025</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-indigo-600 transition">
                    Marketing World Conference
                </h3>
                <p class="text-gray-600 mb-4 line-clamp-2">
                    The ultimate gathering for marketing professionals featuring 
                    workshops, keynotes, and networking opportunities.
                </p>
                <div class="flex items-center text-sm text-gray-500 mb-4">
                    <i data-lucide="map-pin" class="h-4 w-4 mr-1"></i>
                    <span>New York, NY</span>
                </div>
                <div class="flex items-center justify-between pt-4 border-t">
                    <div class="flex -space-x-2">
                        <div class="w-8 h-8 rounded-full bg-gray-300 border-2 border-white"></div>
                        <div class="w-8 h-8 rounded-full bg-gray-400 border-2 border-white"></div>
                    </div>
                    <span class="text-indigo-600 font-semibold text-sm">View Details →</span>
                </div>
            </div>
        </a>

        <!-- Sample Event Card 3 -->
        <a href="/events/3" class="group bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition">
            <div class="h-48 bg-gradient-to-br from-orange-400 to-red-500 group-hover:scale-105 transition-transform duration-300"></div>
            <div class="p-6">
                <div class="flex items-center justify-between mb-3">
                    <span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-full text-xs font-semibold">
                        Festival
                    </span>
                    <span class="text-sm text-gray-500">Feb 10-12, 2025</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-indigo-600 transition">
                    Startup Launch Festival
                </h3>
                <p class="text-gray-600 mb-4 line-clamp-2">
                    Connect with entrepreneurs, investors, and innovators at 
                    the premier startup event in the Southwest.
                </p>
                <div class="flex items-center text-sm text-gray-500 mb-4">
                    <i data-lucide="map-pin" class="h-4 w-4 mr-1"></i>
                    <span>Austin, TX</span>
                </div>
                <div class="flex items-center justify-between pt-4 border-t">
                    <div class="flex -space-x-2">
                        <div class="w-8 h-8 rounded-full bg-gray-300 border-2 border-white"></div>
                        <div class="w-8 h-8 rounded-full bg-gray-400 border-2 border-white"></div>
                        <div class="w-8 h-8 rounded-full bg-gray-500 border-2 border-white"></div>
                    </div>
                    <span class="text-indigo-600 font-semibold text-sm">View Details →</span>
                </div>
            </div>
        </a>

        <!-- Sample Event Card 4 -->
        <a href="/events/4" class="group bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition">
            <div class="h-48 bg-gradient-to-br from-purple-400 to-pink-500 group-hover:scale-105 transition-transform duration-300"></div>
            <div class="p-6">
                <div class="flex items-center justify-between mb-3">
                    <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-xs font-semibold">
                        Meetup
                    </span>
                    <span class="text-sm text-gray-500">Mar 5, 2025</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-indigo-600 transition">
                    Design Thinking Workshop
                </h3>
                <p class="text-gray-600 mb-4 line-clamp-2">
                    Learn design thinking methodologies and apply them to 
                    real-world challenges in this hands-on workshop.
                </p>
                <div class="flex items-center text-sm text-gray-500 mb-4">
                    <i data-lucide="map-pin" class="h-4 w-4 mr-1"></i>
                    <span>Seattle, WA</span>
                </div>
                <div class="flex items-center justify-between pt-4 border-t">
                    <div class="flex -space-x-2">
                        <div class="w-8 h-8 rounded-full bg-gray-300 border-2 border-white"></div>
                        <div class="w-8 h-8 rounded-full bg-gray-400 border-2 border-white"></div>
                    </div>
                    <span class="text-indigo-600 font-semibold text-sm">View Details →</span>
                </div>
            </div>
        </a>

        <!-- Sample Event Card 5 -->
        <a href="/events/5" class="group bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition">
            <div class="h-48 bg-gradient-to-br from-yellow-400 to-orange-500 group-hover:scale-105 transition-transform duration-300"></div>
            <div class="p-6">
                <div class="flex items-center justify-between mb-3">
                    <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-semibold">
                        Online
                    </span>
                    <span class="text-sm text-gray-500">Mar 15, 2025</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-indigo-600 transition">
                    AI & Machine Learning Summit
                </h3>
                <p class="text-gray-600 mb-4 line-clamp-2">
                    Explore the latest advancements in AI and ML with 
                    industry experts and researchers from around the world.
                </p>
                <div class="flex items-center text-sm text-gray-500 mb-4">
                    <i data-lucide="globe" class="h-4 w-4 mr-1"></i>
                    <span>Virtual Event</span>
                </div>
                <div class="flex items-center justify-between pt-4 border-t">
                    <div class="flex -space-x-2">
                        <div class="w-8 h-8 rounded-full bg-gray-300 border-2 border-white"></div>
                        <div class="w-8 h-8 rounded-full bg-gray-400 border-2 border-white"></div>
                        <div class="w-8 h-8 rounded-full bg-gray-500 border-2 border-white"></div>
                        <div class="w-8 h-8 rounded-full bg-gray-600 border-2 border-white"></div>
                    </div>
                    <span class="text-indigo-600 font-semibold text-sm">View Details →</span>
                </div>
            </div>
        </a>

        <!-- Sample Event Card 6 -->
        <a href="/events/6" class="group bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition">
            <div class="h-48 bg-gradient-to-br from-cyan-400 to-blue-500 group-hover:scale-105 transition-transform duration-300"></div>
            <div class="p-6">
                <div class="flex items-center justify-between mb-3">
                    <span class="bg-cyan-100 text-cyan-700 px-3 py-1 rounded-full text-xs font-semibold">
                        Seminar
                    </span>
                    <span class="text-sm text-gray-500">Apr 2, 2025</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-indigo-600 transition">
                    Future of Finance Forum
                </h3>
                <p class="text-gray-600 mb-4 line-clamp-2">
                    Discover emerging trends in fintech, blockchain, and 
                    digital banking with leading financial experts.
                </p>
                <div class="flex items-center text-sm text-gray-500 mb-4">
                    <i data-lucide="map-pin" class="h-4 w-4 mr-1"></i>
                    <span>Chicago, IL</span>
                </div>
                <div class="flex items-center justify-between pt-4 border-t">
                    <div class="flex -space-x-2">
                        <div class="w-8 h-8 rounded-full bg-gray-300 border-2 border-white"></div>
                        <div class="w-8 h-8 rounded-full bg-gray-400 border-2 border-white"></div>
                    </div>
                    <span class="text-indigo-600 font-semibold text-sm">View Details →</span>
                </div>
            </div>
        </a>
    </div>

    <!-- Pagination -->
    <div class="mt-12 flex justify-center">
        <nav class="flex items-center space-x-2">
            <button class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 disabled:opacity-50" disabled>
                Previous
            </button>
            <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg">1</button>
            <button class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50">2</button>
            <button class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50">3</button>
            <span class="text-gray-400">...</span>
            <button class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50">10</button>
            <button class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50">
                Next
            </button>
        </nav>
    </div>
</div>
@endsection
