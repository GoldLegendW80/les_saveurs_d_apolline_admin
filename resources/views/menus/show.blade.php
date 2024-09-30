@extends('layouts.app')

@section('content')
<!-- resources/views/menus/show.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $menu->name }} - Menu Details</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
    <style>
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
        <a href="{{ route('menus.index') }}" class="text-blue-500 hover:text-blue-700 mb-4 inline-block">&larr; Back to Menu List</a>
        
        <h1 class="text-3xl font-bold mb-6">{{ $menu->name }}</h1>
        <p class="mb-6">{{ $menu->description }}</p>

        <button id="addCategoryBtn" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mb-4">
            Add Category
        </button>

        <div id="categoriesList" class="space-y-4">
            @foreach($menu->categories->sortBy('order') as $category)
                <div class="bg-white shadow-md rounded px-8 py-6" data-category-id="{{ $category->id }}">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold">{{ $category->name }}</h3>
                        <div>
                            <button onclick="openEditCategoryModal({{ $category->id }}, '{{ $category->name }}', '{{ $category->description }}')" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-1 px-2 rounded mr-2">
                                Edit
                            </button>
                            <button onclick="deleteCategory({{ $category->id }})" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded">
                                Delete
                            </button>
                        </div>
                    </div>
                    <p class="mb-4">{{ $category->description }}</p>
                    
                    <button onclick="openAddItemModal({{ $category->id }})" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mb-4">
                        Add Item
                    </button>

                    <h4 class="text-lg font-semibold mb-2">Items:</h4>
                    <ul class="list-none pl-0 itemsList" data-category-id="{{ $category->id }}">
                        @foreach($category->items->sortBy('order') as $item)
                            <li data-item-id="{{ $item->id }}" class="mb-2 p-2 bg-gray-100 rounded flex justify-between items-center">
                                <div>
                                    <span class="font-medium">{{ $item->name }}</span> - 
                                    {{ $item->description }} 
                                    <span class="font-semibold">${{ number_format($item->price, 2) }}</span>
                                </div>
                                <div>
                                    <button onclick="openEditItemModal({{ $item->id }}, '{{ $item->name }}', '{{ $item->description }}', {{ $item->price }}, {{ $category->id }})" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-1 px-2 rounded mr-2">
                                        Edit
                                    </button>
                                    <button onclick="deleteItem({{ $item->id }})" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded">
                                        Delete
                                    </button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div id="editCategoryModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="editCategoryForm" class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <input type="hidden" id="editCategoryId" name="id">
                    <div class="mb-4">
                        <label for="editCategoryName" class="block text-gray-700 text-sm font-bold mb-2">Category Name</label>
                        <input type="text" id="editCategoryName" name="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="mb-4">
                        <label for="editCategoryDescription" class="block text-gray-700 text-sm font-bold mb-2">Description</label>
                        <textarea id="editCategoryDescription" name="description" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                    </div>
                    <div class="flex items-center justify-between">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Update Category
                        </button>
                        <button type="button" onclick="closeEditCategoryModal()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add/Edit Item Modal -->
    <div id="itemModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="itemForm" class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <input type="hidden" id="itemId" name="id">
                    <input type="hidden" id="itemCategoryId" name="category_id">
                    <div class="mb-4">
                        <label for="itemName" class="block text-gray-700 text-sm font-bold mb-2">Item Name</label>
                        <input type="text" id="itemName" name="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                    <div class="mb-4">
                        <label for="itemDescription" class="block text-gray-700 text-sm font-bold mb-2">Description</label>
                        <textarea id="itemDescription" name="description" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="itemPrice" class="block text-gray-700 text-sm font-bold mb-2">Price</label>
                        <input type="number" id="itemPrice" name="price" step="0.01" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                    <div class="flex items-center justify-between">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Save Item
                        </button>
                        <button type="button" onclick="closeItemModal()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Cancel
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

        // Enable drag-and-drop for categories
        new Sortable(document.getElementById('categoriesList'), {
            animation: 150,
            handle: '.bg-white',
            onEnd: function() {
                updateCategoryOrder();
            }
        });

        // Enable drag-and-drop for items within each category
        document.querySelectorAll('.itemsList').forEach(function(el) {
            new Sortable(el, {
                animation: 150,
                onEnd: function() {
                    updateItemOrder(el.getAttribute('data-category-id'));
                }
            });
        });

        function updateCategoryOrder() {
            let categories = Array.from(document.querySelectorAll('#categoriesList > div'));
            let order = categories.map((category, index) => ({
                id: category.getAttribute('data-category-id'),
                order: index
            }));

            fetch('{{ route("categories.reorder") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ order: order })
            })
            .then(response => response.json())
            .then(data => {
                console.log(data);
                showToast('Categories reordered successfully');
            })
            .catch((error) => {
                console.error('Error:', error);
                showToast('Error reordering categories', 'error');
            });
        }

        function updateItemOrder(categoryId) {
            let items = Array.from(document.querySelectorAll(`.itemsList[data-category-id="${categoryId}"] > li`));
            let order = items.map((item, index) => ({
                id: item.getAttribute('data-item-id'),
                order: index
            }));

            fetch(`/items/reorder/${categoryId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ order: order })
            })
            .then(response => response.json())
            .then(data => {
                console.log(data);
                showToast('Items reordered successfully');
            })
            .catch((error) => {
                console.error('Error:', error);
                showToast('Error reordering items', 'error');
            });
        }

        function openEditCategoryModal(id, name, description) {
            document.getElementById('editCategoryId').value = id;
            document.getElementById('editCategoryName').value = name;
            document.getElementById('editCategoryDescription').value = description;
            document.getElementById('editCategoryModal').classList.remove('hidden');
        }

        function closeEditCategoryModal() {
            document.getElementById('editCategoryModal').classList.add('hidden');
        }

        document.getElementById('editCategoryForm').addEventListener('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            let categoryId = formData.get('id');

            fetch(`/categories/${categoryId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Category updated successfully');
                    closeEditCategoryModal();
                    location.reload(); // Reload the page to show the updated category
                } else {
                    showToast('Error updating category', 'error');
                }
            });
        });

        document.getElementById('addCategoryBtn').addEventListener('click', function() {
            let categoryName = prompt("Enter the name for the new category:");
            if (categoryName) {
                let formData = new FormData();
                formData.append('name', categoryName);
                formData.append('menu_id', '{{ $menu->id }}');

                fetch('{{ route("categories.store") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Category added successfully');
                        location.reload(); // Reload the page to show the new category
                    } else {
                        showToast('Error creating category', 'error');
                    }
                });
            }
        });

        function deleteCategory(categoryId) {
            if (confirm('Are you sure you want to delete this category? This will also delete all items in this category.')) {
                fetch(`/categories/${categoryId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Category deleted successfully');
                        location.reload(); // Reload the page to reflect the deletion
                    } else {
                        showToast('Error deleting category', 'error');
                    }
                });
            }
        }

        function openAddItemModal(categoryId) {
            document.getElementById('itemId').value = '';
            document.getElementById('itemCategoryId').value = categoryId;
            document.getElementById('itemName').value = '';
            document.getElementById('itemDescription').value = '';
            document.getElementById('itemPrice').value = '';
            document.getElementById('itemModal').classList.remove('hidden');
        }

        function openEditItemModal(id, name, description, price, categoryId) {
            document.getElementById('itemId').value = id;
            document.getElementById('itemCategoryId').value = categoryId;
            document.getElementById('itemName').value = name;
            document.getElementById('itemDescription').value = description;
            document.getElementById('itemPrice').value = price;
            document.getElementById('itemModal').classList.remove('hidden');
        }

        function closeItemModal() {
            document.getElementById('itemModal').classList.add('hidden');
        }

        document.getElementById('itemForm').addEventListener('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            let itemId = formData.get('id');
            let url = itemId ? `/items/${itemId}` : '{{ route("items.store") }}';
            let method = itemId ? 'PUT' : 'POST';

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
                    showToast(itemId ? 'Item updated successfully' : 'Item added successfully');
                    closeItemModal();
                    location.reload(); // Reload the page to show the new/updated item
                } else {
                    showToast(itemId ? 'Error updating item' : 'Error creating item', 'error');
                }
            });
        });

        function deleteItem(itemId) {
            if (confirm('Are you sure you want to delete this item?')) {
                fetch(`/items/${itemId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Item deleted successfully');
                        location.reload(); // Reload the page to reflect the deletion
                    } else {
                        showToast('Error deleting item', 'error');
                    }
                });
            }
        }
    </script>
</body>
</html>
@endsection