window.addEventListener("load", function () {
    const search = window.location.search;
    const params = new URLSearchParams(search);
    const property_id = params.get('property_id');

    var is_interested_image = document.getElementsByClassName("is-interested-image")[0];
    is_interested_image.addEventListener("click", function (event) {
        var XHR = new XMLHttpRequest();

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
        var is_interested_image = document.getElementsByClassName("is-interested-image")[0];
        var interested_user_count = document.getElementsByClassName("interested-user-count")[0];

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
