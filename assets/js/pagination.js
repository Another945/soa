document.addEventListener('DOMContentLoaded', function() {
    // This function will be called for a container of items (products or services)
    function createPagination(containerId, itemsPerPage) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const items = Array.from(container.children);
        const totalItems = items.length;
        if (totalItems <= itemsPerPage) return; // No pagination needed if not enough items

        const totalPages = Math.ceil(totalItems / itemsPerPage);
        const paginationNav = document.createElement('nav');
        paginationNav.setAttribute('aria-label', 'Paginación');
        paginationNav.innerHTML = `<ul class="pagination justify-content-center"></ul>`;
        
        // Insert pagination nav after the items container
        container.after(paginationNav);

        const paginationUl = paginationNav.querySelector('.pagination');

        // Show a specific page
        function showPage(page) {
            items.forEach((item, index) => {
                // Show item if it's within the current page range
                if (index >= (page - 1) * itemsPerPage && index < page * itemsPerPage) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });

            // Update active state on buttons
            const currentActive = paginationUl.querySelector('.page-item.active');
            if (currentActive) currentActive.classList.remove('active');
            const newActive = paginationUl.querySelector(`[data-page='${page}']`);
            if (newActive) newActive.parentElement.classList.add('active');
        }

        // Create page buttons
        for (let i = 1; i <= totalPages; i++) {
            const li = document.createElement('li');
            li.className = 'page-item';
            li.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
            paginationUl.appendChild(li);
        }

        // Add event listener to the pagination container
        paginationUl.addEventListener('click', function(e) {
            e.preventDefault();
            if (e.target.matches('.page-link')) {
                const page = parseInt(e.target.dataset.page);
                showPage(page);
            }
        });

        // Show the first page by default
        showPage(1);
    }

    // Initialize pagination for products and services
    createPagination('productList', 6); // For pages/productos.php
    createPagination('serviceList', 6); // For pages/servicios.php
});