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
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold mb-6">Menu List</h1>
        <div class="mb-4">
            <button onclick="openAddMenuModal()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                Create New Menu
            </button>
        </div>

        <div class="bg-white shadow-md rounded px-8 pt-6 pb-8">
            @if($menus->isEmpty())
                <p>No menus available.</p>
            @else
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left">Name</th>
                            <th class="px-4 py-2 text-left">Description</th>
                            <th class="px-4 py-2 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="menuList">
                        @foreach($menus->sortBy('order') as $menu)
                        <tr data-id="{{ $menu->id }}" class="menu-item">
                            <td class="border px-4 py-2">
                                <span class="cursor-move">&#9776;</span>
                                <a href="{{ route('menus.show', $menu) }}" class="text-blue-500 hover:text-blue-700 ml-2">
                                    {{ $menu->name }}
                                </a>
                            </td>
                            <td class="border px-4 py-2">{{ $menu->description }}</td>
                            <td class="border px-4 py-2">
                                <button onclick="openEditMenuModal({{ $menu->id }}, '{{ $menu->name }}', '{{ $menu->description }}')" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-1 px-2 rounded mr-2">
                                    Edit
                                </button>
                                <button onclick="deleteMenu({{ $menu->id }})" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded">
                                    Delete
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <!-- Add/Edit Menu Modal -->
    <div id="menuModal" class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center">
        <div class="modal-overlay absolute w-full h-full bg-gray-900 opacity-50"></div>
        
        <div class="modal-container bg-white w-11/12 md:max-w-md mx-auto rounded shadow-lg z-50 overflow-y-auto">
            <div class="modal-content py-4 text-left px-6">
                <div class="flex justify-between items-center pb-3">
                    <p class="text-2xl font-bold" id="modalTitle">Add Menu</p>
                    <div class="modal-close cursor-pointer z-50">
                        <svg class="fill-current text-black" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18">
                            <path d="M14.53 4.53l-1.06-1.06L9 7.94 4.53 3.47 3.47 4.53 7.94 9l-4.47 4.47 1.06 1.06L9 10.06l4.47 4.47 1.06-1.06L10.06 9z"></path>
                        </svg>
                    </div>
                </div>

                <form id="menuForm">
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
                    <div class="flex items-center justify-between">
                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                            Save Menu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toaster Container -->
    <div id="toaster"></div>

    <script>
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
            const modal = document.getElementById('menuModal');
            modal.classList.remove('opacity-0', 'pointer-events-none');
            document.body.classList.add('modal-active');
        }

        function closeModal() {
            const modal = document.getElementById('menuModal');
            modal.classList.add('opacity-0', 'pointer-events-none');
            document.body.classList.remove('modal-active');
        }

        function openAddMenuModal() {
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
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(menuId ? 'Menu updated successfully' : 'Menu created successfully');
                    closeModal();
                    location.reload(); // Reload the page to show the changes
                } else {
                    showToast(data.message || 'An error occurred', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred', 'error');
            });
        });

        // Delete menu
        function deleteMenu(menuId) {
            if (confirm('Are you sure you want to delete this menu?')) {
                fetch(`/menus/${menuId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Menu deleted successfully');
                        location.reload(); // Reload the page to reflect the deletion
                    } else {
                        showToast(data.message || 'An error occurred', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred', 'error');
                });
            }
        }

        // Enable drag-and-drop for menu items
        new Sortable(document.getElementById('menuList'), {
            animation: 150,
            handle: '.cursor-move',
            onEnd: function() {
                updateMenuOrder();
            }
        });

        function updateMenuOrder() {
            const menuItems = document.querySelectorAll('.menu-item');
            const order = Array.from(menuItems).map((item, index) => ({
                id: item.dataset.id,
                order: index
            }));

            fetch('/menus/reorder', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ order: order })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Menu order updated successfully');
                } else {
                    showToast('Error updating menu order', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred while updating menu order', 'error');
            });
        }

        // Close modal when clicking outside
        window.addEventListener('click', function(e) {
            const modal = document.getElementById('menuModal');
            if (e.target === modal) {
                closeModal();
            }
        });

        // Close modal when clicking the close button
        document.querySelector('.modal-close').addEventListener('click', closeModal);
    </script>
</body>
</html>

@endsection