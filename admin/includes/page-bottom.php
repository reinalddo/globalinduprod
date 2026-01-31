        </main>
    </div>
    <script>
        (function () {
            const sidebar = document.querySelector('.sidebar');
            const toggle = document.querySelector('.mobile-toggle');
            const handleResize = () => {
                if (window.innerWidth <= 960) {
                    toggle.hidden = false;
                } else {
                    toggle.hidden = true;
                    sidebar.classList.remove('is-open');
                }
            };
            window.addEventListener('resize', handleResize);
            handleResize();
        })();
    </script>
</body>
</html>
