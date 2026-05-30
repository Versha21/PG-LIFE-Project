window.addEventListener("load", function () {
    var is_interested_images = document.getElementsByClassName("is-interested-image");
    Array.from(is_interested_images).forEach(element => {
        element.addEventListener("click", function (event) {
            var XHR = new XMLHttpRequest();
            var property_id = event.target.getAttribute("property_id");

            // On success
            XHR.addEventListener("load", toggle_interested_success);

            // On error
            XHR.addEventListener("error", on_error);

            // Set up request
            XHR.open("GET", "api/toggle_interested.php?property_id=" + property_id);

            // Initiate the request
            XHR.send();

            document.getElementById("loading").style.display = 'block';
            event.preventDefault();
        });
    });

        // Sorting
        var sortDesc = document.getElementById('sort-desc');
        var sortAsc = document.getElementById('sort-asc');
        var container = document.querySelector('.page-container');

        function applySort(order) {
            var cards = Array.from(document.querySelectorAll('.property-card'));
            cards.sort(function (a, b) {
                var ra = parseInt(a.getAttribute('data-rent')) || 0;
                var rb = parseInt(b.getAttribute('data-rent')) || 0;
                return order === 'desc' ? rb - ra : ra - rb;
            });
            cards.forEach(function (c) { container.appendChild(c); });
        }

        if (sortDesc) sortDesc.addEventListener('click', function () { applySort('desc'); });
        if (sortAsc) sortAsc.addEventListener('click', function () { applySort('asc'); });

        // Filtering
        var genderButtons = document.querySelectorAll('.gender-filter-btn');
        function applyFilter(gender) {
            var cards = document.querySelectorAll('.property-card');
            cards.forEach(function (c) {
                var g = (c.getAttribute('data-gender') || '').toLowerCase();
                if (gender === 'none') {
                    c.style.display = '';
                } else if (gender === 'unisex') {
                    c.style.display = (g === 'unisex') ? '' : 'none';
                } else {
                    c.style.display = (g === gender) ? '' : 'none';
                }
            });
        }

        genderButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                genderButtons.forEach(function (b) { b.classList.remove('btn-active'); });
                btn.classList.add('btn-active');
                var g = btn.getAttribute('data-filter') || 'none';
                applyFilter(g);
            });
        });
});

var toggle_interested_success = function (event) {
    document.getElementById("loading").style.display = 'none';

    var response;
    try {
        response = JSON.parse(event.target.responseText);
    } catch (e) {
        console.error('Invalid JSON response', event.target.responseText);
        alert('Unexpected server response. Please try again.');
        return;
    }
    if (response.success) {
        var property_id = response.property_id;
        var is_interested_image = document.querySelector(".property-id-" + property_id + " .is-interested-image");
        var interested_user_count = document.querySelector(".property-id-" + property_id + " .interested-user-count");

        if (is_interested_image) {
            if (response.is_interested) {
                is_interested_image.classList.add("fas");
                is_interested_image.classList.remove("far");
            } else {
                is_interested_image.classList.add("far");
                is_interested_image.classList.remove("fas");
            }
        }

        if (interested_user_count) {
            var current = parseInt(interested_user_count.innerHTML) || 0;
            interested_user_count.innerHTML = response.is_interested ? current + 1 : Math.max(0, current - 1);
        }
    } else if (!response.success && !response.is_logged_in) {
        window.$("#login-modal").modal("show");
    }
};
