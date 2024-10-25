<?php
require_once 'config/config.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Todo App</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
</head>
<body class="bg-gray-100">
    <div id="app" class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <h1 class="text-xl font-bold">Todo App</h1>
                        </div>
                    </div>
                    
                    <div class="flex items-center">
                        <div class="ml-3 relative">
                            <div>
                                <button @click="showProfileMenu = !showProfileMenu" 
                                class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-white">
                                    <span class="sr-only">Open user menu</span>
                                    <img class="h-8 w-8 rounded-full" 
                                         src="https://ui-avatars.com/api/?name=<?php echo htmlspecialchars($_SESSION['username']); ?>" 
                                         alt="Profile">
                                </button>
                            </div>
                            
                            <!-- Profile dropdown -->
                            <div v-if="showProfileMenu" 
                                 class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none">
                                <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Your Profile</a>
                                <a href="auth/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Sign out</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Search and Filter Bar -->
            <div class="mb-6 bg-white shadow rounded-lg p-4">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div class="flex-1 min-w-0 mb-4 md:mb-0 md:mr-4">
                        <input type="text" 
                               v-model="searchQuery" 
                               @input="searchTasks"
                               placeholder="Search tasks..." 
                               class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                    </div>
                    <div class="flex items-center space-x-4">
                        <select v-model="statusFilter" 
                                @change="filterTasks"
                                class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                            <option value="">All Tasks</option>
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                        </select>
                        <button @click="showNewListModal = true" 
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            New List
                        </button>
                    </div>
                </div>
            </div>
            <!-- Todo Lists Grid -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div v-for="list in todoLists" 
                     :key="list.id" 
                     class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">{{ list.title }}</h3>
                            <button @click="deleteList(list.id)" 
                                    class="text-red-600 hover:text-red-900">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Tasks List -->
                        <div class="space-y-3">
                            <div v-for="task in filteredTasks(list.id)" 
                                 :key="task.id" 
                                 class="flex items-center">
                                <input type="checkbox" 
                                       :checked="task.status === 'completed'"
                                       @change="toggleTaskStatus(task)"
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <span :class="{'line-through text-gray-500': task.status === 'completed'}"
                                      class="ml-3 text-sm text-gray-700">
                                    {{ task.title }}
                                </span>
                            </div>
                        </div>

                        <!-- Add Task Button -->
                        <button @click="showNewTaskModal = true; currentListId = list.id" 
                                class="mt-4 w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Add Task
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- New List Modal -->
        <div v-if="showNewListModal" 
             class="fixed z-50 inset-0 overflow-y-auto" 
             aria-labelledby="modal-title" 
             role="dialog" 
             aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                     @click="closeNewListModal"></div>

                <div class="relative inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                New Todo List
                            </h3>
                            <div class="mt-2">
                                <input type="text" 
                                       v-model="newListTitle"
                                       @keyup.enter="createNewList"
                                       required
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                       placeholder="List Title">
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <button type="button"
                                @click="createNewList"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Create List
                        </button>
                        <button type="button"
                                @click="closeNewListModal"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Task Modal -->
        <div v-if="showNewTaskModal" 
             class="fixed z-50 inset-0 overflow-y-auto" 
             aria-labelledby="modal-title" 
             role="dialog" 
             aria-modal="true">
            <!-- Similar structure as New List Modal -->
        </div>
    </div>
    <script>
        const app = Vue.createApp({
            data() {
                return {
                    showProfileMenu: false,
                    showNewListModal: false,
                    showNewTaskModal: false,
                    currentListId: null,
                    newListTitle: '',
                    newTaskTitle: '',
                    searchQuery: '',
                    statusFilter: '',
                    todoLists: [],
                    tasks: []
                }
            },
            methods: {
                closeNewListModal() {
                    this.showNewListModal = false;
                    this.newListTitle = '';
                },
                closeNewTaskModal() {
                    this.showNewTaskModal = false;
                    this.newTaskTitle = '';
                    this.currentListId = null;
                },
                async loadLists() {
                    try {
                        const response = await fetch('todo/list_handler.php');
                        if (!response.ok) throw new Error('Failed to load lists');
                        const data = await response.json();
                        this.todoLists = Array.isArray(data) ? data : [];
                    } catch (error) {
                        console.error('Error loading lists:', error);
                        alert('Failed to load lists. Please try refreshing the page.');
                    }
                },
                async loadTasks() {
                    try {
                        const params = new URLSearchParams({
                            status: this.statusFilter,
                            search: this.searchQuery
                        });
                        const response = await fetch(`todo/task_handler.php?${params}`);
                        if (!response.ok) throw new Error('Failed to load tasks');
                        const data = await response.json();
                        this.tasks = Array.isArray(data) ? data : [];
                    } catch (error) {
                        console.error('Error loading tasks:', error);
                        alert('Failed to load tasks. Please try refreshing the page.');
                    }
                },
                filteredTasks(listId) {
                    return this.tasks.filter(task => task.list_id === listId);
                },
                async createNewList() {
                    if (!this.newListTitle.trim()) {
                        alert('Please enter a list title');
                        return;
                    }
                    
                    try {
                        const formData = new FormData();
                        formData.append('action', 'create');
                        formData.append('title', this.newListTitle.trim());

                        const response = await fetch('todo/list_handler.php', {
                            method: 'POST',
                            body: formData
                        });

                        if (!response.ok) throw new Error('Failed to create list');

                        await this.loadLists();
                        this.closeNewListModal();
                    } catch (error) {
                        console.error('Error creating list:', error);
                        alert('Failed to create list. Please try again.');
                    }
                },
                async createNewTask() {
                    if (!this.newTaskTitle.trim() || !this.currentListId) {
                        alert('Please enter a task title');
                        return;
                    }
                    
                    try {
                        const formData = new FormData();
                        formData.append('action', 'create');
                        formData.append('list_id', this.currentListId);
                        formData.append('title', this.newTaskTitle.trim());

                        const response = await fetch('todo/task_handler.php', {
                            method: 'POST',
                            body: formData
                        });

                        if (!response.ok) throw new Error('Failed to create task');

                        await this.loadTasks();
                        this.closeNewTaskModal();
                    } catch (error) {
                        console.error('Error creating task:', error);
                        alert('Failed to create task. Please try again.');
                    }
                },
                async deleteList(listId) {
                    if (!confirm('Are you sure you want to delete this list? All tasks in this list will also be deleted.')) {
                        return;
                    }

                    try {
                        const formData = new FormData();
                        formData.append('action', 'delete');
                        formData.append('list_id', listId);

                        const response = await fetch('todo/list_handler.php', {
                            method: 'POST',
                            body: formData
                        });

                        if (!response.ok) throw new Error('Failed to delete list');

                        await this.loadLists();
                        await this.loadTasks();
                    } catch (error) {
                        console.error('Error deleting list:', error);
                        alert('Failed to delete list. Please try again.');
                    }
                },
                async toggleTaskStatus(task) {
                    try {
                        const formData = new FormData();
                        formData.append('action', 'update_status');
                        formData.append('task_id', task.id);
                        formData.append('status', task.status === 'completed' ? 'pending' : 'completed');

                        const response = await fetch('todo/task_handler.php', {
                            method: 'POST',
                            body: formData
                        });

                        if (!response.ok) throw new Error('Failed to update task status');

                        await this.loadTasks();
                    } catch (error) {
                        console.error('Error updating task status:', error);
                        alert('Failed to update task status. Please try again.');
                    }
                },
                searchTasks: debounce(async function() {
                    await this.loadTasks();
                }, 300),
                filterTasks() {
                    this.loadTasks();
                }
            },
            mounted() {
                this.loadLists();
                this.loadTasks();

                // Add event listeners for keyboard navigation
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') {
                        this.closeNewListModal();
                        this.closeNewTaskModal();
                    }
                });
            }
        });

        // Utility function for debouncing
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func.apply(this, args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        app.mount('#app');
    </script>
</body>
</html>