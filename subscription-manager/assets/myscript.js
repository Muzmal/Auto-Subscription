
docReady(function() {
    // DOM is loaded and ready for manipulation here
    var checkbox = document.getElementById('muz_sub_add_pro')

    checkbox.addEventListener('change', (event) => {
    if (event.currentTarget.checked) {
        document.getElementsByClassName("muz-sub-pro")[0].style.display="block";
    } else {
        document.getElementsByClassName("muz-sub-pro")[0].style.display="none";
    }
    })
});
function docReady(fn) {
    // see if DOM is already available
    if (document.readyState === "complete" || document.readyState === "interactive") {
        // call on next available tick
        setTimeout(fn, 1);
    } else {
        document.addEventListener("DOMContentLoaded", fn);
    }
}   