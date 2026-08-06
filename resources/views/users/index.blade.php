<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-bold text-slate-800 leading-tight">{{ __('User Management') }}</h1>
            <p class="text-xs text-slate-500 mt-0.5">{{ __('Manage agents, admins and user accounts') }}</p>
        </div>
    </x-slot>

    <!-- Notification Alert -->
    @if(session('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2 sm:translate-y-0 sm:translate-x-2" x-transition:enter-end="opacity-100 transform translate-y-0 sm:translate-x-0" x-transition:leave="transition ease-in duration-205" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed top-5 right-5 z-50 flex items-center p-4 mb-4 text-emerald-800 rounded-xl bg-emerald-50/90 backdrop-blur border border-emerald-200 shadow-xl max-w-md" role="alert">
            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-600 mr-3">
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m7 10 2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
            </div>
            <div class="text-sm font-semibold pr-4">
                {{ session('success') }}
            </div>
            <button @click="show = false" type="button" class="ml-auto text-emerald-600 hover:text-emerald-800 rounded-lg p-1 hover:bg-emerald-100/50 inline-flex items-center justify-center h-6 w-6 cursor-pointer transition" aria-label="Close">
                <span class="sr-only">Close</span>
                <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2 sm:translate-y-0 sm:translate-x-2" x-transition:enter-end="opacity-100 transform translate-y-0 sm:translate-x-0" x-transition:leave="transition ease-in duration-205" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed top-5 right-5 z-50 flex items-center p-4 mb-4 text-rose-800 rounded-xl bg-rose-50/90 backdrop-blur border border-rose-200 shadow-xl max-w-md" role="alert">
            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-rose-500/10 flex items-center justify-center text-rose-600 mr-3">
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
            </div>
            <div class="text-sm font-semibold pr-4">
                {{ session('error') }}
            </div>
            <button @click="show = false" type="button" class="ml-auto text-rose-605 hover:text-rose-800 rounded-lg p-1 hover:bg-rose-100/50 inline-flex items-center justify-center h-6 w-6 cursor-pointer transition" aria-label="Close">
                <span class="sr-only">Close</span>
                <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
            </button>
        </div>
    @endif

    <div class="py-6 px-6 bg-slate-50 min-h-screen" x-data="{
        showFormModal: @json($errors->any() && old('_action') !== 'delete'),
        modalMode: '{{ old('_action', 'create') }}',
        formAction: '{{ old('_action') === 'edit' ? route('users.update', old('user_id', 0)) : route('users.store') }}',
        userId: '{{ old('user_id') }}',
        userName: '{{ old('name') }}',
        userEmail: '{{ old('email') }}',
        userRole: '{{ old('role', 'agent') }}',
        userPassword: '',
        
        showDeleteModal: false,
        deleteAction: '',
        deleteName: '',

        openCreate() {
            this.modalMode = 'create';
            this.formAction = '{{ route('users.store') }}';
            this.userId = '';
            this.userName = '';
            this.userEmail = '';
            this.userRole = 'agent';
            this.userPassword = '';
            this.showFormModal = true;
        },

        openEdit(user) {
            this.modalMode = 'edit';
            this.formAction = '/users/' + user.id;
            this.userId = user.id;
            this.userName = user.name;
            this.userEmail = user.email;
            this.userRole = user.role;
            this.userPassword = '';
            this.showFormModal = true;
        },

        openDelete(user) {
            this.deleteAction = '/users/' + user.id;
            this.deleteName = user.name;
            this.showDeleteModal = true;
        }
    }">
        <div>
            <div class="bg-white overflow-hidden shadow-sm border border-slate-200/60 rounded-xl">
                <!-- Top controls -->
                <div class="p-6 bg-white border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <!-- Search Form -->
                    <form method="GET" action="{{ route('users.index') }}" class="flex items-center w-full md:max-w-md">
                        <div class="relative w-full">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input type="text" name="search" value="{{ $search }}" placeholder="Search by name or email..." class="block w-full pl-10 pr-10 py-2.5 border border-slate-200 rounded-xl bg-slate-50 text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm transition">
                            @if($search)
                                <a href="{{ route('users.index') }}" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-650 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                        <button type="submit" class="ml-2 px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-semibold rounded-xl text-sm transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 cursor-pointer shadow-sm">
                            Search
                        </button>
                    </form>

                    <!-- Create User button -->
                    <div>
                        <button @click="openCreate()" class="w-full md:w-auto inline-flex items-center justify-center px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-semibold rounded-xl text-sm shadow-md shadow-indigo-500/10 hover:shadow-indigo-500/15 transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 cursor-pointer">
                            <svg class="w-4.5 h-4.5 mr-2" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                            </svg>
                            Create User
                        </button>
                    </div>
                </div>

                <!-- Table Content -->
                @if($users->isEmpty())
                    <div class="flex flex-col items-center justify-center py-20 px-4 text-center">
                        <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center mb-4 border border-slate-100 text-slate-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.109A11.386 11.386 0 0110.089 21c-2.243 0-4.352-.648-6.124-1.763b.077-.3.077-.601.077-.899 0-3.185 2.506-5.8 5.602-6.5M12 9a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM19.5 7.5a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800">No users found</h3>
                        <p class="text-sm text-slate-500 mt-1 max-w-sm">
                            @if($search)
                                No registered users matched your search terms. Try refining your search query.
                            @else
                                There are no users registered in the system yet. Click "Create User" to add one.
                            @endif
                        </p>
                        @if($search)
                            <a href="{{ route('users.index') }}" class="mt-4 inline-flex items-center text-sm font-semibold text-indigo-650 hover:text-indigo-500 transition">
                                Clear Search Filter
                            </a>
                        @endif
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-slate-50/75">
                                <tr>
                                    <th scope="col" class="px-4 py-2.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                                        Name
                                    </th>
                                    <th scope="col" class="px-4 py-2.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                                        Email
                                    </th>
                                    <th scope="col" class="px-4 py-2.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                                        Role
                                    </th>
                                    <th scope="col" class="px-4 py-2.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                                        Created At
                                    </th>
                                    <th scope="col" class="px-4 py-2.5 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-100">
                                @foreach ($users as $user)
                                    <tr class="hover:bg-slate-50/50 transition-colors duration-150">
                                        <!-- Name -->
                                        <td class="px-4 py-2.5 whitespace-nowrap">
                                            <div class="flex items-center gap-2.5">
                                                <!-- Avatar initials -->
                                                <div class="flex-shrink-0 w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs select-none
                                                    {{ $user->role === 'admin' ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700' }}">
                                                    {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                                                </div>
                                                <span class="text-sm font-medium text-slate-900">{{ $user->name }}</span>
                                            </div>
                                        </td>

                                        <!-- Email -->
                                        <td class="px-4 py-2.5 whitespace-nowrap">
                                            <span class="text-sm text-slate-600">{{ $user->email }}</span>
                                        </td>

                                        <!-- Role -->
                                        <td class="px-4 py-2.5 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                                {{ $user->role === 'admin' 
                                                    ? 'bg-indigo-50 text-indigo-700' 
                                                    : 'bg-emerald-50 text-emerald-700' }}">
                                                {{ ucfirst($user->role) }}
                                            </span>
                                        </td>

                                        <!-- Created At -->
                                        <td class="px-4 py-2.5 whitespace-nowrap">
                                            <span class="text-sm text-slate-500" title="{{ $user->created_at->toDayDateTimeString() }}">
                                                {{ $user->created_at->format('d M Y') }}
                                            </span>
                                        </td>

                                        <!-- Actions -->
                                        <td class="px-4 py-2.5 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="inline-flex items-center space-x-1.5">
                                                <!-- Edit button -->
                                                <button @click="openEdit({{ $user->toJson() }})" class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-slate-100 transition cursor-pointer" title="Edit user">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                    </svg>
                                                </button>

                                                <!-- Delete button -->
                                                @if($user->id !== auth()->id())
                                                    <button @click="openDelete({{ $user->toJson() }})" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-slate-100 transition cursor-pointer" title="Delete user">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                @else
                                                    <span class="px-2.5 py-1 text-2xs font-semibold text-slate-400 bg-slate-50 rounded-lg select-none" title="You cannot delete your own logged-in account">Self</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/30">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Create/Edit Form Modal -->
        <div x-show="showFormModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Overlay background -->
                <div x-show="showFormModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="showFormModal = false" aria-hidden="true"></div>

                <!-- Center modal container -->
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal card -->
                <div x-show="showFormModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative inline-block align-bottom bg-white rounded-2xl px-6 pt-6 pb-6 text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-150/75">
                    
                    <!-- Close button in top-right -->
                    <div class="absolute top-0 right-0 pt-4 pr-4">
                        <button @click="showFormModal = false" type="button" class="w-8 h-8 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center cursor-pointer transition">
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="sm:flex sm:items-start w-full">
                        <div class="text-center sm:text-left w-full">
                            <h3 class="text-xl font-bold text-slate-900" id="modal-title" x-text="modalMode === 'create' ? 'Create User' : 'Edit User'">
                                Create User
                            </h3>
                            <p class="mt-1 text-sm text-slate-500" x-text="modalMode === 'create' ? 'Create a new administrator or agent account.' : 'Update user profile and account details.'"></p>

                            <!-- Form -->
                            <form :action="formAction" method="POST" class="mt-6 space-y-4">
                                @csrf
                                <input type="hidden" name="_action" :value="modalMode">
                                <input type="hidden" name="user_id" :value="userId">
                                
                                <!-- PUT Method spoofing for Edit -->
                                <template x-if="modalMode === 'edit'">
                                    <input type="hidden" name="_method" value="PUT">
                                </template>

                                <!-- Name input -->
                                <div>
                                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Name</label>
                                    <input type="text" name="name" x-model="userName" required class="block w-full px-3.5 py-2.5 border rounded-xl text-sm bg-slate-50 text-slate-900 focus:outline-none transition {{ $errors->has('name') ? 'border-rose-350 focus:border-rose-500 focus:ring-1 focus:ring-rose-500' : 'border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500' }}">
                                    @error('name')
                                        <p class="text-rose-550 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Email input -->
                                <div>
                                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Email Address</label>
                                    <input type="email" name="email" x-model="userEmail" required class="block w-full px-3.5 py-2.5 border rounded-xl text-sm bg-slate-50 text-slate-900 focus:outline-none transition {{ $errors->has('email') ? 'border-rose-350 focus:border-rose-500 focus:ring-1 focus:ring-rose-500' : 'border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500' }}">
                                    @error('email')
                                        <p class="text-rose-550 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Password input -->
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-500">Password</label>
                                        <span x-show="modalMode === 'edit'" class="text-2xs text-slate-400 font-semibold italic">Optional (leave blank to keep current)</span>
                                    </div>
                                    <input type="password" name="password" x-model="userPassword" :required="modalMode === 'create'" class="block w-full px-3.5 py-2.5 border rounded-xl text-sm bg-slate-50 text-slate-900 focus:outline-none transition {{ $errors->has('password') ? 'border-rose-350 focus:border-rose-500 focus:ring-1 focus:ring-rose-500' : 'border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500' }}">
                                    @error('password')
                                        <p class="text-rose-550 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Role select -->
                                <div>
                                    <label for="role" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Role</label>
                                    <select name="role" x-model="userRole" required class="block w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 text-slate-900 focus:outline-none transition focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                        <option value="agent">Agent</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                    @error('role')
                                        <p class="text-rose-550 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Actions -->
                                <div class="mt-6 flex items-center justify-end space-x-3 pt-5 border-t border-slate-100">
                                    <button type="button" @click="showFormModal = false" class="px-5 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition cursor-pointer">
                                        Cancel
                                    </button>
                                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-sm shadow-md shadow-indigo-500/10 transition cursor-pointer">
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div x-show="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Overlay background -->
                <div x-show="showDeleteModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="showDeleteModal = false" aria-hidden="true"></div>

                <!-- Center modal container -->
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal card -->
                <div x-show="showDeleteModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative inline-block align-bottom bg-white rounded-2xl px-6 pt-6 pb-6 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-100">
                    
                    <!-- Close button in top-right -->
                    <div class="absolute top-0 right-0 pt-4 pr-4">
                        <button @click="showDeleteModal = false" type="button" class="w-8 h-8 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center cursor-pointer transition">
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="sm:flex sm:items-start w-full">
                        <!-- Danger Icon -->
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-11 w-11 rounded-full bg-rose-50 sm:mx-0 sm:h-10 sm:w-10 text-rose-600 border border-rose-100">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg font-bold text-slate-900" id="modal-title">
                                Delete User
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-slate-500">
                                    Are you sure you want to delete user <span class="font-bold text-slate-800" x-text="deleteName"></span>? This user account will be soft-deleted.
                                </p>
                            </div>

                            <!-- Delete Form -->
                            <form :action="deleteAction" method="POST" class="mt-6 flex items-center justify-end space-x-3 pt-5 border-t border-slate-100">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="_action" value="delete">
                                
                                <button type="button" @click="showDeleteModal = false" class="px-5 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition cursor-pointer">
                                    Cancel
                                </button>
                                <button type="submit" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-xl text-sm shadow-md shadow-rose-500/10 transition cursor-pointer">
                                    Delete User
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
