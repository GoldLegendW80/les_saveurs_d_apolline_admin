@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Menu List</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
    <style>
        .modal {
            transition: opacity 0.25s ease;
        }
        body.modal-active {
            overflow-x: hidden;
            overflow-y: visible !important;
        }
        #toaster {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 9999;
        }
        .drag-handle {
            cursor: move;
            cursor: -webkit-grabbing;
        }
        .menu-item {
            transition: background-color 0.3s ease;
        }
        .menu-item:active {
            background-color: #e5e7eb;
        }
        .sortable-ghost {
            opacity: 0.5;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4 sm:p-6">
        <h1 class="text-2xl sm:text-3xl font-bold mb-4 sm:mb-6">Menu List</h1>
        <div class="mb-4">
            <button id="addMenuBtn" class="w-full sm:w-auto bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                Create New Menu
            </button>
        </div>

        <div class="bg-white shadow-md rounded px-4 sm:px-8 pt-6 pb-8">
            @if($menus->isEmpty())
                <p>No menus available.</p>
            @else
                <p class="mb-2 text-sm text-gray-600">Drag and drop to reorder menus</p>
                <ul id="menuList" class="space-y-2">
                    @foreach($menus->sortBy('order') as $menu)
                    <li data-id="{{ $menu->id }}" class="menu-item bg-gray-50 p-4 rounded">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="drag-handle mr-2 text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>
                                </span>
                                <a href="{{ route('menus.show', $menu) }}" class="text-blue-500 hover:text-blue-700">
                                    {{ $menu->name }}
                                </a>
                            </div>
                            <div class="flex space-x-2">
                                <button type="button" class="edit-btn bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-1 px-2 rounded" data-id="{{ $menu->id }}" data-name="{{ $menu->name }}" data-description="{{ $menu->description }}">
                                    Edit
                                </button>
                                <button type="button" class="delete-btn bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded" data-id="{{ $menu->id }}">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <!-- Add/Edit Menu Modal -->
    <div id="menuModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md sm:w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Add Menu</h3>
                <button id="closeModal" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form id="menuForm" class="mt-2">
                <input type="hidden" id="menuId" name="id">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="menuName">
                        Menu Name
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="menuName" type="text" name="name" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="menuDescription">
                        Description
                    </label>
                    <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" id="menuDescription" name="description"></textarea>
                </div>
                <div class="items-center px-4 py-3">
                    <button id="saveMenuBtn" class="px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300" type="submit">
                        Save Menu
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toaster Container -->
    <div id="toaster"></div>

    <!-- Place JavaScript at the end of the body for proper loading -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toaster function
            function showToast(message, type = 'success') {
                const toaster = document.getElementById('toaster');
                const toast = document.createElement('div');
                toast.className = `${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white px-4 py-2 rounded shadow-lg mb-2`;
                toast.textContent = message;
                toaster.appendChild(toast);
                setTimeout(() => {
                    toast.remove();
                }, 3000);
            }

            // Modal functions
            function openModal() {
                document.getElementById('menuModal').classList.remove('hidden');
            }

            function closeModal() {
                document.getElementById('menuModal').classList.add('hidden');
            }

            function openAddMenuModal() {
                console.log('Opening Add Menu Modal');
                document.getElementById('modalTitle').textContent = 'Add Menu';
                document.getElementById('menuId').value = '';
                document.getElementById('menuName').value = '';
                document.getElementById('menuDescription').value = '';
                openModal();
            }

            function openEditMenuModal(id, name, description) {
                document.getElementById('modalTitle').textContent = 'Edit Menu';
                document.getElementById('menuId').value = id;
                document.getElementById('menuName').value = name;
                document.getElementById('menuDescription').value = description;
                openModal();
            }

            // Form submission
            document.getElementById('menuForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const menuId = formData.get('id');
                const url = menuId ? `/menus/${menuId}` : '/menus';
                const method = menuId ? 'PUT' : 'POST';
                fetch(url, {
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        name: formData.get('name'),
                        description: formData.get('description'),
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    closeModal();
                    if (menuId) {
                        showToast('Menu updated successfully');
                    } else {
                        showToast('Menu created successfully');
                    }
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred', 'error');
                });
            });

            // Button event listeners
            document.getElementById('addMenuBtn').addEventListener('click', openAddMenuModal);
            document.getElementById('closeModal').addEventListener('click', closeModal);

            // Edit button functionality
            document.querySelectorAll('.edit-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const name = this.dataset.name;
                    const description = this.dataset.description;
                    openEditMenuModal(id, name, description);
                });
            });

            // Close modal when clicking outside the form
            document.getElementById('menuModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeModal();
                }
            });
        });
    </script>
</body>
</html>
@endsection
