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
        .btn { 
            transition: all 0.3s ease;
            @apply font-bold py-2 px-4 rounded-lg shadow-md;
        }
        .btn:hover { 
            transform: translateY(-1px); 
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); 
        }
        .btn-sm {
            @apply py-1 px-2 text-sm;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <div class="container mx-auto p-4 sm:p-6">
        <a href="{{ route('menus.index') }}" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">&larr; Retour à la Liste des Menus</a>
        
        <h1 class="text-3xl sm:text-4xl font-bold mb-4 text-gray-800">{{ $menu->name }}</h1>
        <p class="mb-6 text-gray-600">{{ $menu->description }}</p>

        <button onclick="openAddCategoryModal({{ $menu->id }})" class="w-full sm:w-auto btn bg-green-500 hover:bg-green-600 text-white mb-6">
            Ajouter une Catégorie
        </button>

        <div id="categoriesList" class="space-y-4">
            @foreach($menu->categories->sortBy('order') as $category)
                <div class="bg-white shadow-md rounded-lg p-4 draggable" data-category-id="{{ $category->id }}" data-name="{{ $category->name }}" data-description="{{ $category->description }}">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4">
                        <div class="flex items-center mb-2 sm:mb-0">
                            <span class="drag-handle mr-2 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </span>
                            <h3 class="text-xl font-bold text-gray-800">{{ $category->name }}</h3>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="openEditCategoryModal({{ $category->id }}, '{{ $category->name }}', '{{ $category->description }}')" class="btn btn-sm bg-yellow-500 hover:bg-yellow-600 text-white">
                                Modifier
                            </button>
                            <button onclick="deleteCategory({{ $category->id }})" class="btn btn-sm bg-red-500 hover:bg-red-600 text-white">
                                Supprimer
                            </button>
                        </div>
                    </div>
                    <p class="mb-4 text-gray-600 text-sm">{{ $category->description }}</p>
                    
                    <button onclick="openAddItemModal({{ $category->id }})" class="w-full sm:w-auto btn bg-blue-500 hover:bg-blue-600 text-white mb-4">
                        Ajouter un Article
                    </button>

                    <h4 class="text-lg font-semibold mb-2 text-gray-700">Articles:</h4>
                    <ul class="space-y-2 itemsList" data-category-id="{{ $category->id }}">
                        @foreach($category->items->sortBy('order') as $item)
                            <li data-item-id="{{ $item->id }}" data-name="{{ $item->name }}" data-description="{{ $item->description }}" data-price="{{ $item->price }}" class="bg-gray-50 rounded-lg p-3 flex flex-col sm:flex-row sm:items-center justify-between draggable">
                                <div class="flex items-center mb-2 sm:mb-0">
                                    <span class="drag-handle mr-2 text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                        </svg>
                                    </span>
                                    <div>
                                        <span class="font-medium text-gray-800">{{ $item->name }}</span>
                                        <p class="text-xs text-gray-600">{{ $item->description }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2 mt-2 sm:mt-0">
                                    <span class="font-semibold text-gray-800 text-sm">{{ number_format($item->price, 2) }}€</span>
                                    <button onclick="openEditItemModal('{{ $item->id }}', '{{ $item->name }}', '{{ $item->description }}', '{{ $item->price }}')" class="btn btn-sm bg-yellow-500 hover:bg-yellow-600 text-white">
                                        Modifier
                                    </button>
                                    <button onclick="deleteItem({{ $item->id }})" class="btn btn-sm bg-red-500 hover:bg-red-600 text-white">
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


    <!-- Add/Edit Category Modal -->
    <div id="categoryModal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center p-4 flex hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md mx-auto relative">
            <!-- Close button -->
            <button onclick="closeCategoryModal()" class="absolute top-2 right-2 text-gray-600 hover:text-gray-900">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            
            <form id="categoryForm" class="px-4 pt-5 pb-4 sm:p-6">
                <input type="hidden" id="categoryId" name="id">
                <input type="hidden" id="categoryMenuId" name="menu_id">
                <div class="mb-4">
                    <label for="categoryName" class="block text-gray-700 text-sm font-bold mb-2">Nom de la Catégorie</label>
                    <input type="text" id="categoryName" name="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label for="categoryDescription" class="block text-gray-700 text-sm font-bold mb-2">Description</label>
                    <textarea id="categoryDescription" name="description" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" rows="3"></textarea>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between gap-3">
                    <button type="button" onclick="closeCategoryModal()" class="w-full sm:w-auto bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-150 ease-in-out">
                        Annuler
                    </button>
                    <button type="submit" class="w-full sm:w-auto bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-150 ease-in-out">
                        Enregistrer la Catégorie
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add/Edit Item Modal -->
    <div id="itemModal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center p-4 flex hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md mx-auto relative">
            <!-- Close button -->
            <button onclick="closeItemModal()" class="absolute top-2 right-2 text-gray-600 hover:text-gray-900">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            
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
                    <button type="button" onclick="closeItemModal()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Annuler
                    </button>    
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Enregistrer l'Article
                    </button>
                </div>
            </form>
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
                showToast('Articles réorganisés avec succès');
            })
            .catch((error) => {
                console.error('Erreur:', error);
                showToast('Erreur lors de la réorganisation des articles', 'error');
            });
        }

        function openAddCategoryModal(menuId) {
            document.getElementById('categoryId').value = '';
            document.getElementById('categoryMenuId').value = menuId;
            document.getElementById('categoryName').value = '';
            document.getElementById('categoryDescription').value = '';
            document.getElementById('categoryModal').classList.remove('hidden');
        }

        function openEditCategoryModal(id, name, description) {
            // Get the latest data from the DOM
            const categoryElement = document.querySelector(`[data-category-id="${id}"]`);
            if (categoryElement) {
                name = categoryElement.getAttribute('data-name');
                description = categoryElement.getAttribute('data-description');
            }
            
            document.getElementById('categoryId').value = id;
            document.getElementById('categoryName').value = name;
            document.getElementById('categoryDescription').value = description;
            document.getElementById('categoryModal').classList.remove('hidden');
        }

        function closeCategoryModal() {
            document.getElementById('categoryModal').classList.add('hidden');
        }

        document.getElementById('categoryForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const categoryId = formData.get('id');
                const url = categoryId ? `/categories/${categoryId}` : '/categories';
                const method = categoryId ? 'PUT' : 'POST';

                fetch(url, {
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        name: formData.get('name'),
                        description: formData.get('description'),
                        menu_id: formData.get('menu_id'),
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    closeCategoryModal();
                    if (categoryId) {
                        updateCategoryInDOM(data.category);
                        showToast('Catégorie mise à jour avec succès');
                    } else {
                        addCategoryToDOM(data.category);
                        showToast('Catégorie créée avec succès');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    showToast('Une erreur est survenue', 'error');
                });
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
                        removeCategoryFromDOM(categoryId);
                        showToast('Catégorie supprimée avec succès');
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

        function openEditItemModal(id, name, description, price) {
            // Get the latest data from the DOM
            const itemElement = document.querySelector(`[data-item-id="${id}"]`);
            if (itemElement) {
                name = itemElement.getAttribute('data-name');
                description = itemElement.getAttribute('data-description');
                price = itemElement.getAttribute('data-price');
            }
            
            document.getElementById('itemId').value = id;
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
            const formData = new FormData(this);
            const itemId = formData.get('id');
            const url = itemId ? `/items/${itemId}` : '/items';
            const method = itemId ? 'PUT' : 'POST';

            fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    name: formData.get('name'),
                    description: formData.get('description'),
                    price: formData.get('price'),
                    category_id: formData.get('category_id'),
                }),
            })
            .then(response => response.json())
            .then(data => {
                closeItemModal();
                if (itemId) {
                    updateItemInDOM(data.item);
                    showToast('Article mis à jour avec succès');
                } else {
                    addItemToDOM(data.item, data.item.category_id);
                    showToast('Article créé avec succès');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showToast('Une erreur est survenue', 'error');
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
                        removeItemFromDOM(itemId);
                        showToast('Article supprimé avec succès');
                    } else {
                        showToast('Erreur lors de la suppression de l\'article', 'error');
                    }
                });
            }
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('categoryModal')) {
                closeCategoryModal();
            }
            if (event.target == document.getElementById('itemModal')) {
                closeItemModal();
            }
        }

        function addCategoryToDOM(category) {
            const categoriesList = document.getElementById('categoriesList');
            const newCategory = document.createElement('div');
            newCategory.className = 'bg-white shadow-md rounded-lg p-4 draggable';
            newCategory.setAttribute('data-category-id', category.id);
            newCategory.setAttribute('data-name', category.name);
            newCategory.setAttribute('data-description', category.description);
            newCategory.innerHTML = `
                <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4">
                    <div class="flex items-center mb-2 sm:mb-0">
                        <span class="drag-handle mr-2 text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </span>
                        <h3 class="text-xl font-bold text-gray-800">${category.name}</h3>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="openEditCategoryModal(${category.id}, '${category.name}', '${category.description}')" class="btn btn-sm bg-yellow-500 hover:bg-yellow-600 text-white">
                            Modifier
                        </button>
                        <button onclick="deleteCategory(${category.id})" class="btn btn-sm bg-red-500 hover:bg-red-600 text-white">
                            Supprimer
                        </button>
                    </div>
                </div>
                <p class="mb-4 text-gray-600 text-sm">${category.description}</p>
                
                <button onclick="openAddItemModal(${category.id})" class="w-full sm:w-auto btn bg-blue-500 hover:bg-blue-600 text-white mb-4">
                    Ajouter un Article
                </button>

                <h4 class="text-lg font-semibold mb-2 text-gray-700">Articles:</h4>
                <ul class="space-y-2 itemsList" data-category-id="${category.id}">
                </ul>
            `;
            categoriesList.appendChild(newCategory);
            
            // Initialize Sortable for the new category's items
            new Sortable(newCategory.querySelector('.itemsList'), {
                animation: 150,
                draggable: '.draggable',
                handle: '.draggable',
                onEnd: function() {
                    updateItemOrder(category.id);
                }
            });
        }

    function updateCategoryInDOM(category) {
        const categoryElement = document.querySelector(`[data-category-id="${category.id}"]`);
        if (categoryElement) {
            categoryElement.querySelector('h3').textContent = category.name;
            categoryElement.querySelector('p').textContent = category.description;
            
            // Update data attributes
            categoryElement.setAttribute('data-name', category.name);
            categoryElement.setAttribute('data-description', category.description);
            
            // Update onclick attribute of the edit button
            const editButton = categoryElement.querySelector('button:nth-child(1)');
            editButton.setAttribute('onclick', `openEditCategoryModal(${category.id}, '${category.name}', '${category.description}')`);
        }
    }

    function removeCategoryFromDOM(categoryId) {
        const categoryElement = document.querySelector(`[data-category-id="${categoryId}"]`);
        if (categoryElement) {
            categoryElement.remove();
        }
    }

    function addItemToDOM(item, categoryId) {
        const itemsList = document.querySelector(`.itemsList[data-category-id="${categoryId}"]`);
        const newItem = document.createElement('li');
        newItem.setAttribute('data-item-id', item.id);
        newItem.setAttribute('data-name', item.name);
        newItem.setAttribute('data-description', item.description);
        newItem.setAttribute('data-price', item.price);
        newItem.className = 'bg-gray-50 rounded-lg p-3 flex flex-col sm:flex-row sm:items-center justify-between draggable';
        newItem.innerHTML = `
            <div class="flex items-center mb-2 sm:mb-0">
                <span class="drag-handle mr-2 text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </span>
                <div>
                    <span class="font-medium text-gray-800">${item.name}</span>
                    <p class="text-xs text-gray-600">${item.description}</p>
                </div>
            </div>
            <div class="flex items-center space-x-2 mt-2 sm:mt-0">
                <span class="font-semibold text-gray-800 text-sm">${Number(item.price).toFixed(2)}€</span>
                <button onclick="openEditItemModal('${item.id}', '${item.name}', '${item.description}', '${item.price}')" class="btn btn-sm bg-yellow-500 hover:bg-yellow-600 text-white">
                    Modifier
                </button>
                <button onclick="deleteItem(${item.id})" class="btn btn-sm bg-red-500 hover:bg-red-600 text-white">
                    Supprimer
                </button>
            </div>`;
        itemsList.appendChild(newItem);
    }

        function updateItemInDOM(item) {
            const itemElement = document.querySelector(`[data-item-id="${item.id}"]`);
            if (itemElement) {
                itemElement.querySelector('.font-medium').textContent = item.name;
                itemElement.querySelector('.text-xs').textContent = item.description;
                itemElement.querySelector('.font-semibold').textContent = `${Number(item.price).toFixed(2)}€`;
                
                // Update data attributes
                itemElement.setAttribute('data-name', item.name);
                itemElement.setAttribute('data-description', item.description);
                itemElement.setAttribute('data-price', item.price);
                
                // Update onclick attribute of the edit button
                const editButton = itemElement.querySelector('button:nth-child(2)');
                editButton.setAttribute('onclick', `openEditItemModal('${item.id}', '${item.name}', '${item.description}', '${item.price}')`);
            }
        }

        function removeItemFromDOM(itemId) {
            const itemElement = document.querySelector(`[data-item-id="${itemId}"]`);
            if (itemElement) {
                itemElement.remove();
            }
        }
    </script>
</body>
</html>
@endsection