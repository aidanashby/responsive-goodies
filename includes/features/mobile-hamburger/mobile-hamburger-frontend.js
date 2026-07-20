(function () {
    function tagModules() {
        document.querySelectorAll('.rg-nmh').forEach(function (ul) {
            var mod = ul.closest('.et_pb_fullwidth_menu, .et_pb_menu');
            if (mod) {
                mod.classList.add('no-mobile-hamburger');
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', tagModules);
    } else {
        tagModules();
    }
})();
