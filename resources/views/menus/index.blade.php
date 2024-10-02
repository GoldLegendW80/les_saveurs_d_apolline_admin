@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Liste des Menus</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
    <style>
        .modal { transition: opacity 0.25s ease; }
        body.modal-active { overflow-x: hidden; overflow-y: visible !important; }
        #toaster { position: fixed; top: 1rem; right: 1rem; z-index: 9999; }
        .menu-item { transition: all 0.3s ease; cursor: move; }
        .menu-item:hover { transform: translateY(-2px); box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
        .sortable-ghost { opacity: 0.5; }
        .btn { 
            transition: all 0.3s ease;
            @apply font-bold py-2 px-4 rounded-md shadow-md;
        }
        .btn:hover { 
            transform: translateY(-1px); 
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); 
        }
        .btn-sm {
            @apply py-1 px-2 text-sm;
        }
        .drag-icon { cursor: move; }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <div class="container mx-auto p-6 sm:p-10">
        <h1 class="text-3xl sm:text-4xl font-bold mb-8 text-gray-800">Liste des Menus</h1>
        <div class="mb-6">
        <button onclick="openAddMenuModal()" class="w-full sm:w-auto btn bg-green-500 hover:bg-green-600 text-white">
            Créer un Nouveau Menu
        </button>
        </div>

        <div class="bg-white shadow-md rounded-lg px-4 sm:px-6 py-6">
            @if($menus->isEmpty())
                <p class="text-gray-600">Aucun menu disponible.</p>
            @else
                <p class="mb-4 text-sm text-gray-600">Glissez et déposez pour réorganiser les menus</p>
                <ul id="menuList" class="space-y-4">
                    @foreach($menus->sortBy('order') as $menu)
                    <li data-id="{{ $menu->id }}" class="menu-item bg-gray-50 p-4 rounded-lg">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between">
                            <div class="flex items-center mb-2 sm:mb-0">
                                <span class="drag-icon mr-3 text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>
                                </span>
                                <a href="{{ route('menus.show', $menu) }}" class="text-blue-600 hover:text-blue-800 font-semibold text-lg">
                                    {{ $menu->name }}
                                </a>
                            </div>
                            <div class="flex space-x-2 mt-2 sm:mt-0">
                                <button onclick="openEditMenuModal({{ $menu->id }}, '{{ $menu->name }}', '{{ $menu->description }}')" class="btn btn-sm edit-btn bg-yellow-500 hover:bg-yellow-600 text-white">
                                    Modifier
                                </button>
                                <button onclick="deleteMenu({{ $menu->id }})" class="btn btn-sm delete-btn bg-red-500 hover:bg-red-600 text-white">
                                    Supprimer
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
    <div id="menuModal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center p-4 flex hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md mx-auto relative">
            <!-- Close button -->
            <button onclick="closeModal()" class="absolute top-2 right-2 text-gray-600 hover:text-gray-900">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            
            <form id="menuForm" class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <input type="hidden" id="menuId" name="id">
                <div class="mb-4">
                    <label for="menuName" class="block text-gray-700 text-sm font-bold mb-2">Nom du Menu</label>
                    <input type="text" id="menuName" name="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label for="menuDescription" class="block text-gray-700 text-sm font-bold mb-2">Description</label>
                    <textarea id="menuDescription" name="description" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                </div>
                <div class="flex items-center justify-between">
                    <button type="button" onclick="closeModal()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Annuler
                    </button>    
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Enregistrer le menu
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

        new Sortable(document.getElementById('menuList'), {
            animation: 150,
            onEnd: function() {
                updateMenuOrder();
            }
        });

        function updateMenuOrder() {
            let menus = Array.from(document.querySelectorAll('#menuList > li'));
            let order = menus.map((menu, index) => ({
                id: menu.getAttribute('data-id'),
                order: index
            }));

            fetch('{{ route("menus.reorder") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ order: order })
            })
            .then(response => response.json())
            .then(data => {
                showToast('Menus réorganisés avec succès');
            })
            .catch((error) => {
                console.error('Erreur:', error);
                showToast('Erreur lors de la réorganisation des menus', 'error');
            });
        }

        function closeModal() {
            document.getElementById('menuModal').classList.add('hidden');
        }

        function openAddMenuModal() {
            document.getElementById('menuId').value = '';
            document.getElementById('menuName').value = '';
            document.getElementById('menuDescription').value = '';
            document.getElementById('menuModal').classList.remove('hidden');
        }

        function openEditMenuModal(id, name, description) {
            document.getElementById('menuId').value = id;
            document.getElementById('menuName').value = name;
            document.getElementById('menuDescription').value = description;
            document.getElementById('menuModal').classList.remove('hidden');
        }

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
                    updateMenuInDOM(data.menu);
                } else {
                    addMenuToDOM(data.menu);
                }
                showToast(menuId ? 'Menu mis à jour avec succès' : 'Menu créé avec succès');
            })
            .catch(error => {
                console.error('Erreur:', error);
                showToast('Une erreur est survenue', 'error');
            });
        });

        function updateMenuInDOM(menu) {
            const menuItem = document.querySelector(`li[data-id="${menu.id}"]`);
            if (menuItem) {
                menuItem.querySelector('a').textContent = menu.name;
                // Update other fields if necessary
            }
        }

        function addMenuToDOM(menu) {
            const menuList = document.getElementById('menuList');
            const newMenuItem = document.createElement('li');
            newMenuItem.setAttribute('data-id', menu.id);
            newMenuItem.className = 'menu-item bg-gray-50 p-4 rounded-lg';
            newMenuItem.innerHTML = `
                <div class="flex flex-col sm:flex-row sm:items-center justify-between">
                    <div class="flex items-center mb-2 sm:mb-0">
                        <span class="drag-icon mr-3 text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </span>
                        <a href="/menus/${menu.id}" class="text-blue-600 hover:text-blue-800 font-semibold text-lg">
                            ${menu.name}
                        </a>
                    </div>
                    <div class="flex space-x-2 mt-2 sm:mt-0">
                        <button onclick="openEditMenuModal(${menu.id}, '${menu.name}', '${menu.description}')" class="btn btn-sm edit-btn bg-yellow-500 hover:bg-yellow-600 text-white">
                            Modifier
                        </button>
                        <button onclick="deleteMenu(${menu.id})" class="btn btn-sm delete-btn bg-red-500 hover:bg-red-600 text-white">
                            Supprimer
                        </button>
                    </div>
                </div>
            `;
            menuList.appendChild(newMenuItem);

            // If this is the first menu, remove the "Aucun menu disponible" message
            const noMenuMessage = document.querySelector('.bg-white.shadow-md.rounded-lg p');
            if (noMenuMessage && noMenuMessage.textContent.trim() === 'Aucun menu disponible.') {
                noMenuMessage.remove();
            }

            // Reinitialize Sortable for the new item
            new Sortable(menuList, {
                animation: 150,
                onEnd: function() {
                    updateMenuOrder();
                }
            });
        }

        function deleteMenu(menuId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce menu ?')) {
                fetch(`/menus/${menuId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const menuItem = document.querySelector(`li[data-id="${menuId}"]`);
                    if (menuItem) {
                        menuItem.remove();
                        showToast('Menu supprimé avec succès');

                        // If this was the last menu, show the "Aucun menu disponible" message
                        const menuList = document.getElementById('menuList');
                        if (menuList.children.length === 0) {
                            const noMenuMessage = document.createElement('p');
                            noMenuMessage.className = 'text-gray-600';
                            noMenuMessage.textContent = 'Aucun menu disponible.';
                            menuList.parentNode.insertBefore(noMenuMessage, menuList);
                        }
                    }
                } else {
                    showToast('Erreur lors de la suppression du menu', 'error');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showToast('Erreur lors de la suppression du menu', 'error');
            });
            }
        }

        document.getElementById('menuModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>
@endsection