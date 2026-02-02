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

            const socialSection = document.getElementById('sociales');
            if (socialSection) {
                const gridStyles = 'margin-top:12px;display:grid;gap:18px;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));';
                const cardStyles = 'border:1px solid #e5e7eb;border-radius:12px;padding:18px;background:#ffffff;box-shadow:0 6px 20px rgba(15,23,42,0.07);display:flex;flex-direction:column;gap:12px;';
                const feedbackBox = socialSection.querySelector('[data-social-feedback]');

                const escapeHtml = (value) => {
                    if (typeof value !== 'string') {
                        return '';
                    }
                    return value.replace(/[&<>"']/g, (char) => {
                        switch (char) {
                            case '&':
                                return '&amp;';
                            case '<':
                                return '&lt;';
                            case '>':
                                return '&gt;';
                            case '"':
                                return '&quot;';
                            case "'":
                                return '&#39;';
                            default:
                                return char;
                        }
                    });
                };

                const clearFeedback = () => {
                    if (!feedbackBox) {
                        return;
                    }
                    feedbackBox.style.display = 'none';
                    feedbackBox.innerHTML = '';
                };

                const showSuccess = (message) => {
                    if (!feedbackBox) {
                        return;
                    }
                    feedbackBox.style.display = 'block';
                    const safeMessage = escapeHtml(message || 'Cambios guardados correctamente.');
                    feedbackBox.innerHTML = '<div class="empty-state" style="background:#dcfce7;color:#166534;text-align:left;">' + safeMessage + '</div><div style="height:16px;"></div>';
                };

                const showErrors = (errors) => {
                    if (!feedbackBox) {
                        return;
                    }
                    const items = Array.isArray(errors) && errors.length ? errors : ['Ocurrió un error. Intenta nuevamente.'];
                    const html = items.map((item) => '<li>' + escapeHtml(String(item)) + '</li>').join('');
                    feedbackBox.style.display = 'block';
                    feedbackBox.innerHTML = '<div class="empty-state" style="background:#fee2e2;color:#b91c1c;text-align:left;"><ul style="margin:0;padding-left:18px;">' + html + '</ul></div><div style="height:16px;"></div>';
                };

                const refreshEmptyState = () => {
                    const grid = socialSection.querySelector('[data-social-grid]');
                    const cards = grid ? grid.querySelectorAll('[data-social-card]') : null;
                    let emptyState = socialSection.querySelector('[data-social-empty]');
                    if (!grid || !cards || cards.length === 0) {
                        if (grid && cards && cards.length === 0) {
                            grid.remove();
                        }
                        if (!emptyState) {
                            emptyState = document.createElement('div');
                            emptyState.className = 'empty-state';
                            emptyState.setAttribute('data-social-empty', '');
                            emptyState.textContent = 'No hay redes sociales configuradas.';
                            const createForm = socialSection.querySelector('form[data-social-create]');
                            const createWrapper = createForm ? createForm.parentElement : null;
                            if (createWrapper) {
                                socialSection.insertBefore(emptyState, createWrapper);
                            } else {
                                socialSection.appendChild(emptyState);
                            }
                        }
                    } else if (emptyState) {
                        emptyState.remove();
                    }
                };

                const getGrid = () => {
                    let grid = socialSection.querySelector('[data-social-grid]');
                    if (!grid) {
                        grid = document.createElement('div');
                        grid.setAttribute('data-social-grid', '');
                        grid.setAttribute('style', gridStyles);
                        const emptyState = socialSection.querySelector('[data-social-empty]');
                        if (emptyState) {
                            emptyState.remove();
                        }
                        const createForm = socialSection.querySelector('form[data-social-create]');
                        const createWrapper = createForm ? createForm.parentElement : null;
                        if (createWrapper) {
                            socialSection.insertBefore(grid, createWrapper);
                        } else {
                            socialSection.appendChild(grid);
                        }
                    }
                    return grid;
                };

                const updateCardFromData = (card, social) => {
                    if (!card || !social) {
                        return;
                    }
                    card.setAttribute('data-social-id', String(social.id));
                    const title = card.querySelector('strong');
                    if (title) {
                        title.textContent = 'Editar ' + social.name;
                    }
                    const nameInput = card.querySelector('input[name="name"]');
                    if (nameInput) {
                        nameInput.value = social.name;
                    }
                    const urlInput = card.querySelector('input[name="url"]');
                    if (urlInput) {
                        urlInput.value = social.url;
                    }
                    const hiddenId = card.querySelector('input[name="social_id"]');
                    if (hiddenId) {
                        hiddenId.value = String(social.id);
                    }
                    const fileInput = card.querySelector('input[type="file"]');
                    if (fileInput) {
                        fileInput.value = '';
                    }
                    const previewImg = card.querySelector('img');
                    if (previewImg && social.icon_url) {
                        previewImg.src = social.icon_url;
                        previewImg.alt = social.name;
                        if (social.icon_key) {
                            previewImg.style.objectFit = '';
                        } else {
                            previewImg.style.objectFit = 'contain';
                        }
                    }
                    const iconInfo = card.querySelector('[data-current-icon-path]');
                    if (!social.is_default) {
                        if (social.icon_path) {
                            if (iconInfo) {
                                iconInfo.textContent = 'Icono actual: ' + social.icon_path;
                            } else if (fileInput && fileInput.parentElement) {
                                const span = document.createElement('span');
                                span.setAttribute('data-current-icon-path', '');
                                span.style.fontSize = '0.8rem';
                                span.style.color = '#6b7280';
                                span.style.wordBreak = 'break-all';
                                span.textContent = 'Icono actual: ' + social.icon_path;
                                fileInput.parentElement.appendChild(span);
                            }
                        } else if (iconInfo) {
                            iconInfo.remove();
                        }
                    } else if (iconInfo) {
                        iconInfo.remove();
                    }
                };

                const buildSocialCard = (social) => {
                    const template = document.createElement('template');
                    const imgStyle = social.icon_key ? 'width:22px;height:22px;' : 'width:22px;height:22px;object-fit:contain;';
                    const iconMarkup = social.icon_url ? '<span style="display:inline-flex;width:42px;height:42px;border-radius:8px;background:#f3f4f6;align-items:center;justify-content:center;"><img src="' + escapeHtml(social.icon_url) + '" alt="' + escapeHtml(social.name) + '" style="' + imgStyle + '"></span>' : '';
                    const iconFieldMarkup = social.is_default
                        ? '<span style="font-size:0.8rem;color:#6b7280;">El icono de esta red es fijo.</span>'
                        : '<div style="display:flex;flex-direction:column;gap:6px;"><label>Icono (opcional)</label><input type="file" name="icon_file" accept="image/png,image/svg+xml,image/webp">' + (social.icon_path ? '<span style="font-size:0.8rem;color:#6b7280;word-break:break-all;" data-current-icon-path>Icono actual: ' + escapeHtml(social.icon_path) + '</span>' : '') + '</div>';
                    const deleteFormMarkup = social.is_default
                        ? ''
                        : '<form method="post" style="margin:0;" class="delete-social-form" data-social-delete><input type="hidden" name="form_type" value="social_delete"><input type="hidden" name="social_id" value="' + social.id + '"><button class="btn btn-secondary" type="submit" style="width:100%;background:#f9fafb;color:#b91c1c;border:1px solid #f87171;">Eliminar</button></form>';

                    template.innerHTML = '<div style="' + cardStyles + '" data-social-card data-social-id="' + social.id + '"><form method="post" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:12px;" data-social-form><input type="hidden" name="form_type" value="social_update"><input type="hidden" name="social_id" value="' + social.id + '"><div style="display:flex;align-items:center;gap:12px;">' + iconMarkup + '<strong style="font-size:1rem;color:#111111;">Editar ' + escapeHtml(social.name) + '</strong></div><div style="display:flex;flex-direction:column;gap:6px;"><label>Nombre</label><input type="text" name="name" value="' + escapeHtml(social.name) + '" maxlength="120" required></div><div style="display:flex;flex-direction:column;gap:6px;"><label>Enlace</label><input type="url" name="url" value="' + escapeHtml(social.url) + '" required placeholder="https://"></div>' + iconFieldMarkup + '<div style="display:flex;gap:10px;"><button class="btn btn-primary" type="submit" style="flex:1;">Guardar</button></div></form>' + deleteFormMarkup + '</div>';
                    return template.content.firstElementChild;
                };

                const bindSocialUpdateForm = (form) => {
                    if (!form || form.dataset.ajaxBound === '1') {
                        return;
                    }
                    form.dataset.ajaxBound = '1';
                    form.addEventListener('submit', (event) => {
                        event.preventDefault();
                        if (form.dataset.ajaxBusy === '1') {
                            return;
                        }
                        clearFeedback();
                        form.dataset.ajaxBusy = '1';
                        const submitButton = form.querySelector('button[type="submit"]');
                        const originalText = submitButton ? submitButton.textContent : '';
                        if (submitButton) {
                            submitButton.disabled = true;
                            submitButton.textContent = 'Guardando...';
                        }
                        const formData = new FormData(form);
                        fetch(form.getAttribute('action') || window.location.href, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        }).then(async (response) => {
                            let data = null;
                            try {
                                data = await response.json();
                            } catch (error) {
                                data = null;
                            }
                            if (!response.ok || !data) {
                                showErrors(['No se pudo procesar la respuesta del servidor.']);
                                return;
                            }
                            if (!data.success) {
                                showErrors(data.errors || ['No se pudo guardar los cambios.']);
                                return;
                            }
                            showSuccess(data.message || 'Red social actualizada correctamente.');
                            updateCardFromData(form.closest('[data-social-card]'), data.social);
                        }).catch(() => {
                            showErrors(['No se pudo contactar al servidor. Intenta nuevamente.']);
                        }).finally(() => {
                            form.dataset.ajaxBusy = '0';
                            if (submitButton) {
                                submitButton.disabled = false;
                                submitButton.textContent = originalText;
                            }
                        });
                    });
                };

                const bindSocialDeleteForm = (form) => {
                    if (!form || form.dataset.ajaxBound === '1') {
                        return;
                    }
                    form.dataset.ajaxBound = '1';
                    form.removeAttribute('onsubmit');
                    form.addEventListener('submit', (event) => {
                        event.preventDefault();
                        if (form.dataset.ajaxBusy === '1') {
                            return;
                        }
                        if (!window.confirm('¿Eliminar esta red social?')) {
                            return;
                        }
                        clearFeedback();
                        form.dataset.ajaxBusy = '1';
                        const submitButton = form.querySelector('button[type="submit"]');
                        const originalText = submitButton ? submitButton.textContent : '';
                        if (submitButton) {
                            submitButton.disabled = true;
                            submitButton.textContent = 'Eliminando...';
                        }
                        const formData = new FormData(form);
                        fetch(form.getAttribute('action') || window.location.href, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        }).then(async (response) => {
                            let data = null;
                            try {
                                data = await response.json();
                            } catch (error) {
                                data = null;
                            }
                            if (!response.ok || !data) {
                                showErrors(['No se pudo procesar la respuesta del servidor.']);
                                return;
                            }
                            if (!data.success) {
                                showErrors(data.errors || ['No se pudo eliminar la red social.']);
                                return;
                            }
                            const card = form.closest('[data-social-card]');
                            if (card && card.parentElement) {
                                card.parentElement.removeChild(card);
                            }
                            showSuccess(data.message || 'Red social eliminada correctamente.');
                            refreshEmptyState();
                        }).catch(() => {
                            showErrors(['No se pudo contactar al servidor. Intenta nuevamente.']);
                        }).finally(() => {
                            form.dataset.ajaxBusy = '0';
                            if (submitButton) {
                                submitButton.disabled = false;
                                submitButton.textContent = originalText;
                            }
                        });
                    });
                };

                const bindSocialCreateForm = (form) => {
                    if (!form || form.dataset.ajaxBound === '1') {
                        return;
                    }
                    form.dataset.ajaxBound = '1';
                    form.addEventListener('submit', (event) => {
                        event.preventDefault();
                        if (form.dataset.ajaxBusy === '1') {
                            return;
                        }
                        clearFeedback();
                        form.dataset.ajaxBusy = '1';
                        const submitButton = form.querySelector('button[type="submit"]');
                        const originalText = submitButton ? submitButton.textContent : '';
                        if (submitButton) {
                            submitButton.disabled = true;
                            submitButton.textContent = 'Guardando...';
                        }
                        const formData = new FormData(form);
                        fetch(form.getAttribute('action') || window.location.href, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        }).then(async (response) => {
                            let data = null;
                            try {
                                data = await response.json();
                            } catch (error) {
                                data = null;
                            }
                            if (!response.ok || !data) {
                                showErrors(['No se pudo procesar la respuesta del servidor.']);
                                return;
                            }
                            if (!data.success) {
                                showErrors(data.errors || ['No se pudo registrar la red social.']);
                                return;
                            }
                            showSuccess(data.message || 'Red social añadida correctamente.');
                            const grid = getGrid();
                            const card = buildSocialCard(data.social);
                            grid.appendChild(card);
                            const cardForm = card.querySelector('form[data-social-form]');
                            bindSocialUpdateForm(cardForm);
                            const deleteForm = card.querySelector('[data-social-delete]');
                            if (deleteForm) {
                                bindSocialDeleteForm(deleteForm);
                            }
                            form.reset();
                            refreshEmptyState();
                        }).catch(() => {
                            showErrors(['No se pudo contactar al servidor. Intenta nuevamente.']);
                        }).finally(() => {
                            form.dataset.ajaxBusy = '0';
                            if (submitButton) {
                                submitButton.disabled = false;
                                submitButton.textContent = originalText;
                            }
                        });
                    });
                };

                socialSection.querySelectorAll('form[data-social-form]').forEach((form) => {
                    bindSocialUpdateForm(form);
                });
                socialSection.querySelectorAll('form[data-social-delete]').forEach((form) => {
                    bindSocialDeleteForm(form);
                });
                const createForm = socialSection.querySelector('form[data-social-create]');
                if (createForm) {
                    bindSocialCreateForm(createForm);
                }
            }

            document.querySelectorAll('.empty-state').forEach((el) => {
                el.classList.add('text-center');
            });
        });
    </script>
</body>
</html>
