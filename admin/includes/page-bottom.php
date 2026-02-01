        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.querySelector('.sidebar');
            const toggle = document.querySelector('.mobile-toggle');
            const handleResize = () => {
                if (window.innerWidth <= 992) {
                    toggle.hidden = false;
                } else {
                    toggle.hidden = true;
                    sidebar.classList.remove('is-open');
                }
            };
            window.addEventListener('resize', handleResize);
            handleResize();

            document.querySelectorAll('form').forEach((form) => {
                form.classList.add('row', 'g-3');
                const actions = form.querySelector('.form-actions');
                if (actions) {
                    actions.classList.add('d-flex', 'gap-3', 'flex-wrap', 'mt-1');
                }
                form.querySelectorAll('label').forEach((label) => {
                    const hasChoiceInput = label.querySelector('input[type="checkbox"], input[type="radio"]');
                    if (hasChoiceInput) {
                        label.classList.add('form-check-label', 'd-flex', 'align-items-center', 'gap-2');
                    } else {
                        label.classList.add('form-label', 'fw-semibold');
                    }
                });
                form.querySelectorAll('input[type="text"], input[type="number"], input[type="url"], input[type="email"], input[type="password"], input[type="tel"], input[type="date"], input[type="datetime-local"], input[type="file"], textarea').forEach((control) => {
                    control.classList.add('form-control');
                    const wrappingLabel = control.closest('label');
                    if (wrappingLabel) {
                        wrappingLabel.classList.add('w-100');
                    }
                });
                form.querySelectorAll('select').forEach((select) => {
                    select.classList.add('form-select');
                });
                form.querySelectorAll('input[type="checkbox"], input[type="radio"]').forEach((check) => {
                    check.classList.add('form-check-input', 'me-2');
                });
            });

            document.querySelectorAll('.btn').forEach((button) => {
                if (button.classList.contains('btn-primary')) {
                    button.classList.remove('btn-primary');
                    button.classList.add('btn-dark');
                }
                if (button.classList.contains('btn-outline')) {
                    button.classList.remove('btn-outline');
                    button.classList.add('btn-outline-dark');
                }
            });

            document.querySelectorAll('table').forEach((tbl) => {
                tbl.classList.add('table', 'table-hover', 'align-middle');
                const parent = tbl.parentElement;
                if (parent && !parent.classList.contains('table-responsive')) {
                    const wrapper = document.createElement('div');
                    wrapper.classList.add('table-responsive');
                    parent.insertBefore(wrapper, tbl);
                    wrapper.appendChild(tbl);
                }
            });

            document.querySelectorAll('.empty-state').forEach((el) => {
                el.classList.add('text-center');
            });
        });
    </script>
</body>
</html>
