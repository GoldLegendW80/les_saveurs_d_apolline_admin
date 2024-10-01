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
        .btn { transition: all 0.3s ease; }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); }
        .drag-icon { cursor: move; }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <div class="container mx-auto p-6 sm:p-10">
        <h1 class="text-3xl sm:text-4xl font-bold mb-8 text-gray-800">Liste des Menus</h1>
        <div class="mb-6">
            <button id="addMenuBtn" class="btn bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded-lg shadow-md">
                Créer un Nouveau Menu
            </button>
        </div>

        <div class="bg-white shadow-lg rounded-lg px-8 py-6">
            @if($menus->isEmpty())
                <p class="text-gray-600">Aucun menu disponible.</p>
            @else
                <p class="mb-4 text-sm text-gray-600">Glissez et déposez pour réorganiser les menus</p>
                <ul id="menuList" class="space-y-4">
                    @foreach($menus->sortBy('order') as $menu)
                    <li data-id="{{ $menu->id }}" class="menu-item bg-gray-50 p-5 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center flex-grow">
                                <span class="drag-icon mr-3 text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>
                                </span>
                                <a href="{{ route('menus.show', $menu) }}" class="text-blue-600 hover:text-blue-800 font-semibold text-lg">
                                    {{ $menu->name }}
                                </a>
                            </div>
                            <div class="flex space-x-3">
                                <button type="button" class="btn edit-btn bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded-md" data-id="{{ $menu->id }}" data-name="{{ $menu->name }}" data-description="{{ $menu->description }}">
                                    Modifier
                                </button>
                                <button type="button" class="btn delete-btn bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-md" data-id="{{ $menu->id }}">
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
    <div id="menuModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-8 border w-full max-w-md sm:w-96 shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-semibold text-gray-900" id="modalTitle">Ajouter un Menu</h3>
                <button id="closeModal" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form id="menuForm" class="mt-4">
                <input type="hidden" id="menuId" name="id">
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="menuName">
                        Nom du Menu
                    </label>
                    <input class="shadow appearance-none border rounded-md w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="menuName" type="text" name="name" required>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="menuDescription">
                        Description
                    </label>
                    <textarea class="shadow appearance-none border rounded-md w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="menuDescription" name="description" rows="4"></textarea>
                </div>
                <div class="flex items-center justify-end">
                    <button id="saveMenuBtn" class="btn bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-md" type="submit">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toaster Container -->
    <div id="toaster"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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

            function openModal() {
                document.getElementById('menuModal').classList.remove('hidden');
            }

            function closeModal() {
                document.getElementById('menuModal').classList.add('hidden');
            }

            function openAddMenuModal() {
                document.getElementById('modalTitle').textContent = 'Ajouter un Menu';
                document.getElementById('menuId').value = '';
                document.getElementById('menuName').value = '';
                document.getElementById('menuDescription').value = '';
                openModal();
            }

            function openEditMenuModal(id, name, description) {
                document.getElementById('modalTitle').textContent = 'Modifier le Menu';
                document.getElementById('menuId').value = id;
                document.getElementById('menuName').value = name;
                document.getElementById('menuDescription').value = description;
                openModal();
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
                    showToast(menuId ? 'Menu mis à jour avec succès' : 'Menu créé avec succès');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    showToast('Une erreur est survenue', 'error');
                });
            });

            document.getElementById('addMenuBtn').addEventListener('click', openAddMenuModal);
            document.getElementById('closeModal').addEventListener('click', closeModal);

            document.querySelectorAll('.edit-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation(); // Prevent triggering drag
                    openEditMenuModal(this.dataset.id, this.dataset.name, this.dataset.description);
                });
            });

            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation(); // Prevent triggering drag
                    if (confirm('Êtes-vous sûr de vouloir supprimer ce menu ?')) {
                        deleteMenu(this.getAttribute('data-id'));
                    }
                });
            });

            function deleteMenu(menuId) {
                fetch(`/menus/${menuId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Menu supprimé avec succès');
                        document.querySelector(`li[data-id="${menuId}"]`).remove();
                    } else {
                        showToast('Erreur lors de la suppression du menu', 'error');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    showToast('Erreur lors de la suppression du menu', 'error');
                });
            }

            document.getElementById('menuModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeModal();
                }
            });

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
                    console.log(data);
                    showToast('Menus réorganisés avec succès');
                })
                .catch((error) => {
                    console.error('Erreur:', error);
                    showToast('Erreur lors de la réorganisation des menus', 'error');
                });
            }
        });
    </script>
</body>
</html>
@endsection