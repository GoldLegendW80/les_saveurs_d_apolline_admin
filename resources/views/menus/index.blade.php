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
        . flex space-between bg-red-500 { 
            transition: all 0.3s ease; 
            cursor: move;
            @apply bg-blue-50 p-2 rounded-md mb-2 flex items-center justify-between;
        }
        . flex space-between bg-red-500:hover { 
            transform: translateX(2px); 
            @apply bg-blue-100;
        }
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
                <li data-id="{{ $menu->id }}" data-name="{{ $menu->name }}" data-description="{{ $menu->description }}" class="menu-item bg-gray-50 p-4 rounded-lg">
                    <div class="flex flex-col space-y-4">
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
                        
                        <!-- Formulas Section -->
                        <div class="pl-9">
                            <button onclick="openAddFormulaModal({{ $menu->id }})" class="w-full sm:w-auto btn btn-sm bg-indigo-500 hover:bg-indigo-600 text-white mb-3">
                                Ajouter une formule
                            </button>
                            <ul class="formula-list space-y-2" data-menu-id="{{ $menu->id }}">
                                @foreach($menu->formulas->sortBy('order') as $formula)
                                <li class="bg-gray-50 rounded-lg p-3 flex flex-col sm:flex-row sm:items-center justify-between draggable formula-item" data-id="{{ $formula->id }}">
                                    <div class="flex items-center mb-2 sm:mb-0">
                                        <span class="drag-icon mr-2 text-gray-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                            </svg>
                                        </span>
                                        <div>
                                            <span class="font-medium text-gray-800">{{ $formula->name }}</span>
                                            <span class="text-sm text-gray-600 ml-2">{{ number_format($formula->price, 2) }}€</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2 mt-2 sm:mt-0">
                                        <button onclick="openEditFormulaModal({{ $formula->id }}, '{{ $formula->name }}', {{ $formula->price }})" class="btn btn-sm bg-yellow-500 hover:bg-yellow-600 text-white">
                                            Modifier
                                        </button>
                                        <button onclick="deleteFormula({{ $formula->id }})" class="btn btn-sm bg-red-500 hover:bg-red-600 text-white">
                                            Supprimer
                                        </button>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </li>
                @endforeach
            </ul>
        @endif
    </div>

    <!-- Formula Modal -->
    <div id="formulaModal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center p-4 flex hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md mx-auto relative">
            <button onclick="closeFormulaModal()" class="absolute top-2 right-2 text-gray-600 hover:text-gray-900">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            
            <form id="formulaForm" class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <input type="hidden" id="formulaId" name="id">
                <input type="hidden" id="menuId" name="menu_id">
                <div class="mb-4">
                    <label for="formulaName" class="block text-gray-700 text-sm font-bold mb-2">Nom de la formule</label>
                    <input type="text" id="formulaName" name="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label for="formulaPrice" class="block text-gray-700 text-sm font-bold mb-2">Prix</label>
                    <input type="number" step="0.01" id="formulaPrice" name="price" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="flex items-center justify-between">
                    <button type="button" onclick="closeFormulaModal()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Annuler
                    </button>    
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Enregistrer la formule
                    </button>
                </div>
            </form>
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

        const menuList = document.getElementById('menuList');
        if (menuList) {
            new Sortable(menuList, {
                animation: 150,
                onEnd: function() {
                    updateMenuOrder();
                }
            });
        }

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
            // Get the latest data from the DOM
            const menuItem = document.querySelector(`li[data-id="${id}"]`);
            if (menuItem) {
                name = menuItem.getAttribute('data-name');
                description = menuItem.getAttribute('data-description');
            }
            
            document.getElementById('menuId').value = id;
            document.getElementById('menuName').value = name;
            document.getElementById('menuDescription').value = description;
            document.getElementById('menuModal').classList.remove('hidden');
        }

        document.getElementById('menuForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const menuId = document.getElementById('menuId').value || null;
            
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
                    console.log("Updating menu:", menuId); // Debug log
                    updateMenuInDOM(data.menu);
                } else {
                    console.log("Creating new menu"); // Debug log
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
                // Update the visible text
                menuItem.querySelector('a').textContent = menu.name;
                
                // Update the data attributes for editing
                menuItem.setAttribute('data-name', menu.name);
                menuItem.setAttribute('data-description', menu.description);
                
                // Update the onclick attribute of the edit button
                const editButton = menuItem.querySelector('.edit-btn');
                editButton.setAttribute('onclick', `openEditMenuModal(${menu.id}, '${menu.name}', '${menu.description}')`);
            }
        }

        function addMenuToDOM(menu) {
            let menuList = document.getElementById('menuList');
            const container = document.querySelector('.bg-white.shadow-md.rounded-lg');
            
            // If this is the first menu, we need to create the menuList and remove the "no menu" message
            if (!menuList) {
                // Remove the "Aucun menu disponible" message
                const noMenuMessage = container.querySelector('p');
                if (noMenuMessage) {
                    noMenuMessage.remove();
                }

                // Create the help text and menuList
                const helpText = document.createElement('p');
                helpText.className = 'mb-4 text-sm text-gray-600';
                helpText.textContent = 'Glissez et déposez pour réorganiser les menus';
                
                menuList = document.createElement('ul');
                menuList.id = 'menuList';
                menuList.className = 'space-y-4';

                container.appendChild(helpText);
                container.appendChild(menuList);

                // Initialize Sortable for the new menuList
                new Sortable(menuList, {
                    animation: 150,
                    onEnd: function() {
                        updateMenuOrder();
                    }
                });
            }

            const newMenuItem = document.createElement('li');
            newMenuItem.setAttribute('data-id', menu.id);
            newMenuItem.setAttribute('data-name', menu.name);
            newMenuItem.setAttribute('data-description', menu.description);
            newMenuItem.className = 'menu-item bg-gray-50 p-4 rounded-lg';
            newMenuItem.innerHTML = `
                <div class="flex flex-col space-y-4">
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
                    
                    <!-- Formulas Section -->
                    <div class="pl-9">
                        <button onclick="openAddFormulaModal(${menu.id})" class="btn btn-sm bg-indigo-500 hover:bg-indigo-600 text-white mb-3">
                            Ajouter une formule
                        </button>
                        <ul class="formula-list space-y-2" data-menu-id="${menu.id}">
                        </ul>
                    </div>
                </div>
            `;
            menuList.appendChild(newMenuItem);

            // Initialize Sortable for the new formula list
            const newFormulaList = newMenuItem.querySelector('.formula-list');
            new Sortable(newFormulaList, {
                animation: 150,
                group: 'formulas',
                onEnd: function() {
                    updateFormulaOrder(menu.id);
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

        // Initialize Sortable for all formula lists
        const formulaLists = document.querySelectorAll('.formula-list');
        if (formulaLists.length > 0) {
            formulaLists.forEach(list => {
                new Sortable(list, {
                    animation: 150,
                    group: 'formulas',
                    onEnd: function() {
                        updateFormulaOrder(list.getAttribute('data-menu-id'));
                    }
                });
            });
        }

        function updateFormulaOrder(menuId) {
            const formulas = Array.from(document.querySelector(`.formula-list[data-menu-id="${menuId}"]`).children);
            const order = formulas.map((formula, index) => ({
                id: formula.getAttribute('data-id'),
                order: index
            }));

            fetch('/formulas/reorder', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ order: order })
            })
            .then(response => response.json())
            .then(data => {
                showToast('Formules réorganisées avec succès');
            })
            .catch(error => {
                console.error('Erreur:', error);
                showToast('Erreur lors de la réorganisation des formules', 'error');
            });
        }

        function closeFormulaModal() {
            document.getElementById('formulaModal').classList.add('hidden');
        }

        function openAddFormulaModal(menuId) {
            document.getElementById('formulaId').value = '';
            document.getElementById('menuId').value = menuId;
            document.getElementById('formulaName').value = '';
            document.getElementById('formulaPrice').value = '';
            document.getElementById('formulaModal').classList.remove('hidden');
        }

        function openEditFormulaModal(id, name, price) {
            document.getElementById('formulaId').value = id;
            document.getElementById('formulaName').value = name;
            document.getElementById('formulaPrice').value = price;
            document.getElementById('formulaModal').classList.remove('hidden');
        }

        document.getElementById('formulaForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const formulaId = formData.get('id');
            const url = formulaId ? `/formulas/${formulaId}` : '/formulas';
            const method = formulaId ? 'PUT' : 'POST';

            fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    name: formData.get('name'),
                    price: formData.get('price'),
                    menu_id: formData.get('menu_id')
                }),
            })
            .then(response => response.json())
            .then(data => {
                closeFormulaModal();
                if (formulaId) {
                    updateFormulaInDOM(data.formula);
                } else {
                    addFormulaToDOM(data.formula);
                }
                showToast(formulaId ? 'Formule mise à jour avec succès' : 'Formule créée avec succès');
            })
            .catch(error => {
                console.error('Erreur:', error);
                showToast('Une erreur est survenue', 'error');
            });
        });

        function updateFormulaInDOM(formula) {
            const formulaItem = document.querySelector(`li[data-id="${formula.id}"]`);
            if (formulaItem) {
                const nameElement = formulaItem.querySelector('.font-medium');
                const priceElement = formulaItem.querySelector('.text-gray-600');
                
                nameElement.textContent = formula.name;
                priceElement.textContent = `${Number(formula.price).toFixed(2)}€`;
                
                const editButton = formulaItem.querySelector('button:first-child');
                editButton.setAttribute('onclick', `openEditFormulaModal(${formula.id}, '${formula.name}', ${formula.price})`);
            }
        }

        function addFormulaToDOM(formula) {
            const formulaList = document.querySelector(`.formula-list[data-menu-id="${formula.menu_id}"]`);
            const newFormulaItem = document.createElement('li');
            newFormulaItem.className = 'bg-gray-50 rounded-lg p-3 flex flex-col sm:flex-row sm:items-center justify-between draggable formula-item';
            newFormulaItem.setAttribute('data-id', formula.id);
            newFormulaItem.innerHTML = `
                <div class="flex items-center mb-2 sm:mb-0">
                    <span class="drag-icon mr-2 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </span>
                    <div>
                        <span class="font-medium text-gray-800">${formula.name}</span>
                        <span class="text-sm text-gray-600 ml-2">${Number(formula.price).toFixed(2)}€</span>
                    </div>
                </div>
                <div class="flex items-center space-x-2 mt-2 sm:mt-0">
                    <button onclick="openEditFormulaModal(${formula.id}, '${formula.name}', ${formula.price})" class="btn btn-sm bg-yellow-500 hover:bg-yellow-600 text-white">
                        Modifier
                    </button>
                    <button onclick="deleteFormula(${formula.id})" class="btn btn-sm bg-red-500 hover:bg-red-600 text-white">
                        Supprimer
                    </button>
                </div>
            `;
            formulaList.appendChild(newFormulaItem);
        }

        function deleteFormula(formulaId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette formule ?')) {
                fetch(`/formulas/${formulaId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const formulaItem = document.querySelector(`li[data-id="${formulaId}"]`);
                        if (formulaItem) {
                            formulaItem.remove();
                            showToast('Formule supprimée avec succès');
                        }
                    } else {
                        showToast('Erreur lors de la suppression de la formule', 'error');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    showToast('Erreur lors de la suppression de la formule', 'error');
                });
            }
        }

        // Add event listener for the formula modal background click
        document.getElementById('formulaModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeFormulaModal();
            }
        });

        // Prevent menu modal from closing when clicking on the formula modal
        document.getElementById('formulaModal').addEventListener('click', function(e) {
            e.stopPropagation();
        });
    </script>
</body>
</html>
@endsection