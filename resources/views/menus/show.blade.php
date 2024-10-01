@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $menu->name }} - Détails du Menu</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
    <style>
        .modal { transition: opacity 0.25s ease; }
        body.modal-active { overflow-x: hidden; overflow-y: visible !important; }
        #toaster { position: fixed; top: 1rem; right: 1rem; z-index: 9999; }
        .drag-handle { cursor: move; }
        .draggable { 
            transition: all 0.3s ease; 
            cursor: move;
        }
        .draggable:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); 
        }
        .sortable-ghost { opacity: 0.5; }
        .btn { transition: all 0.3s ease; }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <div class="container mx-auto p-6 sm:p-10">
        <a href="{{ route('menus.index') }}" class="text-blue-600 hover:text-blue-800 mb-6 inline-block">&larr; Retour à la Liste des Menus</a>
        
        <h1 class="text-4xl font-bold mb-6 text-gray-800">{{ $menu->name }}</h1>
        <p class="mb-8 text-gray-600">{{ $menu->description }}</p>

        <button id="addCategoryBtn" class="btn bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded-lg shadow-md mb-8">
            Ajouter une Catégorie
        </button>

        <div id="categoriesList" class="space-y-6">
            @foreach($menu->categories->sortBy('order') as $category)
                <div class="bg-white shadow-lg rounded-lg p-6 draggable" data-category-id="{{ $category->id }}">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <span class="drag-handle mr-3 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </span>
                            <h3 class="text-2xl font-bold text-gray-800">{{ $category->name }}</h3>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="openEditCategoryModal({{ $category->id }}, '{{ $category->name }}', '{{ $category->description }}')" class="btn bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded-md">
                                Modifier
                            </button>
                            <button onclick="deleteCategory({{ $category->id }})" class="btn bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-md">
                                Supprimer
                            </button>
                        </div>
                    </div>
                    <p class="mb-6 text-gray-600">{{ $category->description }}</p>
                    
                    <button onclick="openAddItemModal({{ $category->id }})" class="btn bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-md mb-6">
                        Ajouter un Article
                    </button>

                    <h4 class="text-xl font-semibold mb-4 text-gray-700">Articles:</h4>
                    <ul class="space-y-3 itemsList" data-category-id="{{ $category->id }}">
                        @foreach($category->items->sortBy('order') as $item)
                            <li data-item-id="{{ $item->id }}" class="bg-gray-50 rounded-lg p-4 flex items-center justify-between draggable">
                                <div class="flex items-center flex-grow">
                                    <span class="drag-handle mr-3 text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                        </svg>
                                    </span>
                                    <div>
                                        <span class="font-medium text-gray-800">{{ $item->name }}</span>
                                        <p class="text-sm text-gray-600">{{ $item->description }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="font-semibold text-gray-800">{{ number_format($item->price, 2) }}€</span>
                                    <button onclick="openEditItemModal({{ $item->id }}, '{{ $item->name }}', '{{ $item->description }}', {{ $item->price }}, {{ $category->id }})" class="btn bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-1 px-2 rounded-md">
                                        Modifier
                                    </button>
                                    <button onclick="deleteItem({{ $item->id }})" class="btn bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-2 rounded-md">
                                        Supprimer
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
                        <label for="editCategoryName" class="block text-gray-700 text-sm font-bold mb-2">Nom de la Catégorie</label>
                        <input type="text" id="editCategoryName" name="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="mb-4">
                        <label for="editCategoryDescription" class="block text-gray-700 text-sm font-bold mb-2">Description</label>
                        <textarea id="editCategoryDescription" name="description" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                    </div>
                    <div class="flex items-center justify-between">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Mettre à jour la Catégorie
                        </button>
                        <button type="button" onclick="closeEditCategoryModal()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Annuler
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
                        <label for="itemName" class="block text-gray-700 text-sm font-bold mb-2">Nom de l'Article</label>
                        <input type="text" id="itemName" name="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                    <div class="mb-4">
                        <label for="itemDescription" class="block text-gray-700 text-sm font-bold mb-2">Description</label>
                        <textarea id="itemDescription" name="description" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="itemPrice" class="block text-gray-700 text-sm font-bold mb-2">Prix</label>
                        <input type="number" id="itemPrice" name="price" step="0.01" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                    <div class="flex items-center justify-between">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Enregistrer l'Article
                        </button>
                        <button type="button" onclick="closeItemModal()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toaster Container -->
    <div id="toaster"></div>

    <script>
        function showToast(message, type = 'success') {
            const toaster = document.getElementById('toaster');
            const toast = document.createElement('div');
            toast.className = `${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white px-6 py-4 rounded-lg shadow-lg mb-4`;
            toast.textContent = message;
            toaster.appendChild(toast);
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        new Sortable(document.getElementById('categoriesList'), {
            animation: 150,
            draggable: '.draggable',
            handle: '.draggable',
            onEnd: function() {
                updateCategoryOrder();
            }
        });

        document.querySelectorAll('.itemsList').forEach(function(el) {
            new Sortable(el, {
                animation: 150,
                draggable: '.draggable',
                handle: '.draggable',
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
                showToast('Catégories réorganisées avec succès');
            })
            .catch((error) => {
                console.error('Erreur:', error);
                showToast('Erreur lors de la réorganisation des catégories', 'error');
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
                showToast('Articles réorganisés avec succès');
            })
            .catch((error) => {
                console.error('Erreur:', error);
                showToast('Erreur lors de la réorganisation des articles', 'error');
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
                    showToast('Catégorie mise à jour avec succès');
                    closeEditCategoryModal();
                    location.reload();
                } else {
                    showToast('Erreur lors de la mise à jour de la catégorie', 'error');
                }
            });
        });

        document.getElementById('addCategoryBtn').addEventListener('click', function() {
            let categoryName = prompt("Entrez le nom de la nouvelle catégorie:");
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
                        showToast('Catégorie ajoutée avec succès');
                        location.reload();
                    } else {
                        showToast('Erreur lors de la création de la catégorie', 'error');
                    }
                });
            }
        });

        function deleteCategory(categoryId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ? Cela supprimera également tous les articles de cette catégorie.')) {
                fetch(`/categories/${categoryId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Catégorie supprimée avec succès');
                        location.reload();
                    } else {
                        showToast('Erreur lors de la suppression de la catégorie', 'error');
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
                    showToast(itemId ? 'Article mis à jour avec succès' : 'Article ajouté avec succès');
                    closeItemModal();
                    location.reload();
                } else {
                    showToast(itemId ? 'Erreur lors de la mise à jour de l\'article' : 'Erreur lors de la création de l\'article', 'error');
                }
            });
        });

        function deleteItem(itemId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cet article ?')) {
                fetch(`/items/${itemId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Article supprimé avec succès');
                        location.reload();
                    } else {
                        showToast('Erreur lors de la suppression de l\'article', 'error');
                    }
                });
            }
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('editCategoryModal')) {
                closeEditCategoryModal();
            }
            if (event.target == document.getElementById('itemModal')) {
                closeItemModal();
            }
        }
    </script>
</body>
</html>
@endsection