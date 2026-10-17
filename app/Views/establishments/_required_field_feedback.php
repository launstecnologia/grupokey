<?php
$establishmentValidationErrors = (isset($establishmentValidationErrors) && is_array($establishmentValidationErrors))
    ? array_values($establishmentValidationErrors)
    : [];
?>
    (function() {
            const serverValidationErrors = <?= json_encode($establishmentValidationErrors, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?>;

        if (!document.getElementById('establishment-field-error-style')) {
            const style = document.createElement('style');
            style.id = 'establishment-field-error-style';
            style.textContent = ''
                + '.establishment-form .field-invalid,'
                + '.establishment-form .document-dropzone.field-invalid{'
                + 'border-color:#dc2626 !important;'
                + 'box-shadow:0 0 0 1px #dc2626 !important;'
                + '}'
                + '.establishment-form .field-error-msg{'
                + 'margin-top:.35rem;margin-bottom:0;font-size:.875rem;line-height:1.25rem;'
                + 'color:#dc2626;font-weight:600;'
                + '}'
                + '.dark .establishment-form .field-error-msg{color:#fecaca;}'
                + '.establishment-form input,.establishment-form select,.establishment-form textarea{scroll-margin-top:96px;}';
            document.head.appendChild(style);
        }

        function isFieldHidden(el) {
            if (!el || el.disabled || el.type === 'hidden') {
                return true;
            }
            if (el.closest('.hidden, [hidden]')) {
                return true;
            }
            return el.getClientRects().length === 0;
        }

        function fieldWrapper(el) {
            let node = el.parentElement;
            while (node && node !== formEstabelecimento) {
                if (node.querySelector(':scope > label')) {
                    return node;
                }
                node = node.parentElement;
            }
            return el.parentElement;
        }

        function clearFieldError(el) {
            if (!el) {
                return;
            }
            el.classList.remove('field-invalid');
            el.removeAttribute('aria-invalid');
            if (el.type === 'file') {
                const dropzone = el.closest('.document-dropzone');
                if (dropzone) {
                    dropzone.classList.remove('field-invalid');
                }
            }
            const wrapper = fieldWrapper(el);
            const msg = wrapper ? wrapper.querySelector(':scope > .field-error-msg') : null;
            if (msg) {
                msg.remove();
            }
        }

        function showFieldError(el, message) {
            const wrapper = fieldWrapper(el);
            el.classList.add('field-invalid');
            el.setAttribute('aria-invalid', 'true');
            if (el.type === 'file') {
                const dropzone = el.closest('.document-dropzone');
                if (dropzone) {
                    dropzone.classList.add('field-invalid');
                }
            }
            if (!wrapper) {
                return;
            }
            let msg = wrapper.querySelector(':scope > .field-error-msg');
            if (!msg) {
                msg = document.createElement('p');
                msg.className = 'field-error-msg';
                msg.setAttribute('role', 'alert');
                wrapper.appendChild(msg);
            }
            msg.textContent = message;
        }

        function showSectionError(section, message) {
            if (!section) {
                return;
            }
            section.classList.add('section-invalid');
            let msg = section.querySelector(':scope > .field-error-msg');
            if (!msg) {
                msg = document.createElement('p');
                msg.className = 'field-error-msg';
                msg.setAttribute('role', 'alert');
                const heading = section.querySelector('h3, h4');
                if (heading) {
                    heading.insertAdjacentElement('afterend', msg);
                } else {
                    section.prepend(msg);
                }
            }
            msg.textContent = message;
        }

        function clearAllFieldErrors() {
            formEstabelecimento.querySelectorAll('.field-invalid').forEach(function(el) {
                el.classList.remove('field-invalid');
                el.removeAttribute('aria-invalid');
            });
            formEstabelecimento.querySelectorAll('.section-invalid').forEach(function(el) {
                el.classList.remove('section-invalid');
            });
            formEstabelecimento.querySelectorAll('.field-error-msg').forEach(function(msg) {
                msg.remove();
            });
        }

        function requiredFieldMessage(el) {
            if (el.type === 'file') {
                const item = el.closest('.documento-item');
                const select = item ? item.querySelector('select[name="document_type[]"]') : null;
                const option = select && select.selectedOptions ? select.selectedOptions[0] : null;
                const docName = option && option.value ? option.textContent.replace(/\s+/g, ' ').trim() : '';
                return docName ? ('Anexe o documento ' + docName) : 'Anexe o documento obrigatório';
            }
            if (el.type === 'email' && String(el.value || '').trim() !== '') {
                return 'Informe um e-mail válido';
            }
            const wrapper = fieldWrapper(el);
            const label = wrapper ? wrapper.querySelector(':scope > label') : null;
            const name = label ? label.textContent.replace(/\*/g, '').replace(/\s+/g, ' ').trim() : '';
            return name ? ('Preencha o campo ' + name) : 'Preencha este campo';
        }

        function ensureVisibleBusinessRequired() {
            const cnpj = formEstabelecimento.querySelector('[name="cnpj"]');
            const razao = formEstabelecimento.querySelector('[name="razao_social"]');
            const cpf = formEstabelecimento.querySelector('[name="cpf"]');
            [cnpj, razao].forEach(function(el) {
                if (!el) {
                    return;
                }
                const pj = el.closest('.pj-only');
                if (pj && !pj.classList.contains('hidden')) {
                    el.setAttribute('required', 'required');
                } else if (pj) {
                    el.removeAttribute('required');
                }
            });
            if (cpf) {
                const pf = document.getElementById('pf-fields');
                if (pf && !pf.classList.contains('hidden')) {
                    cpf.setAttribute('required', 'required');
                } else {
                    cpf.removeAttribute('required');
                }
            }
        }

        function isInvalidEmail(value) {
            return !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
        }

        function collectInvalidFields() {
            ensureVisibleBusinessRequired();
            const fields = Array.from(formEstabelecimento.querySelectorAll('input, select, textarea'));
            const seenRadio = new Set();
            const invalid = [];

            fields.forEach(function(el) {
                if (!el.hasAttribute('required') || isFieldHidden(el)) {
                    return;
                }
                if (el.type === 'radio') {
                    if (seenRadio.has(el.name)) {
                        return;
                    }
                    seenRadio.add(el.name);
                    const group = formEstabelecimento.querySelectorAll('input[type="radio"][name="' + CSS.escape(el.name) + '"]');
                    const checked = Array.from(group).some(function(radio) {
                        return radio.checked;
                    });
                    if (!checked) {
                        invalid.push(el);
                    }
                    return;
                }
                if (el.type === 'checkbox') {
                    if (!el.checked) {
                        invalid.push(el);
                    }
                    return;
                }
                if (el.type === 'file') {
                    if (!el.files || !el.files.length) {
                        invalid.push(el);
                    }
                    return;
                }
                const value = String(el.value || '').trim();
                if (value === '' || (el.type === 'email' && isInvalidEmail(value))) {
                    invalid.push(el);
                }
            });

            return invalid;
        }

        function missingProductSection() {
            const selected = formEstabelecimento.querySelector('input[name="products[]"]:checked, input[name="dynamic_products[]"]:checked');
            if (selected) {
                return null;
            }
            return document.getElementById('manual-products-section');
        }

        function scrollToElement(target) {
            if (!target) {
                return;
            }
            const scroller = document.getElementById('main-content');
            if (scroller) {
                const top = scroller.scrollTop + (target.getBoundingClientRect().top - scroller.getBoundingClientRect().top) - 24;
                scroller.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
                return;
            }
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function scrollToInvalidField(el) {
            if (!el || !el.matches || !el.matches('input, select, textarea')) {
                scrollToElement(el);
                return;
            }
            const wrapper = fieldWrapper(el) || el;
            const target = el.type === 'file'
                ? (el.closest('.documento-item') || wrapper)
                : wrapper;
            scrollToElement(target);
            window.setTimeout(function() {
                if (el.type !== 'file' && typeof el.focus === 'function') {
                    el.focus({ preventScroll: true });
                }
            }, 350);
        }

        function firstTarget(invalidFields, productSection) {
            const field = invalidFields.length ? invalidFields[0] : null;
            if (field && productSection) {
                const fieldNode = fieldWrapper(field) || field;
                const productFirst = productSection.compareDocumentPosition(fieldNode) & Node.DOCUMENT_POSITION_FOLLOWING;
                return productFirst ? productSection : field;
            }
            return field || productSection;
        }

        function releaseHiddenRequired() {
            formEstabelecimento.querySelectorAll('input, select, textarea').forEach(function(el) {
                if (el.hasAttribute('required') && isFieldHidden(el)) {
                    el.removeAttribute('required');
                }
            });
        }

        function normalizeLabel(text) {
            return String(text || '').replace(/\*/g, '').replace(/\s+/g, ' ').trim().toLowerCase();
        }

        function findFieldByLabelText(labelText) {
            const wanted = normalizeLabel(labelText);
            if (!wanted) {
                return null;
            }
            const labels = Array.from(formEstabelecimento.querySelectorAll('label'));
            let fallback = null;
            for (let i = 0; i < labels.length; i++) {
                const label = labels[i];
                if (normalizeLabel(label.textContent) !== wanted) {
                    continue;
                }
                const container = label.parentElement;
                const field = container ? container.querySelector('input, select, textarea') : null;
                if (!field) {
                    continue;
                }
                if (!isFieldHidden(field)) {
                    return field;
                }
                fallback = fallback || field;
            }
            return fallback;
        }

        function findDocumentField(docLabel) {
            const wanted = normalizeLabel(docLabel);
            const selects = Array.from(formEstabelecimento.querySelectorAll('select[name="document_type[]"]'));
            for (let i = 0; i < selects.length; i++) {
                const select = selects[i];
                const option = select.selectedOptions && select.selectedOptions[0];
                const current = option ? normalizeLabel(option.textContent) : '';
                if (current && current === wanted) {
                    const item = select.closest('.documento-item');
                    const fileInput = item ? item.querySelector('input[type="file"]') : null;
                    return fileInput || select;
                }
            }
            return null;
        }

        function findDocumentsSection() {
            const container = document.getElementById('documentos-section') || document.getElementById('documentos-container');
            if (container) {
                return container;
            }
            const headings = Array.from(document.querySelectorAll('h3, h4'));
            for (let i = 0; i < headings.length; i++) {
                if (/documentos/i.test(headings[i].textContent || '')) {
                    return headings[i].closest('.mb-8') || headings[i];
                }
            }
            return null;
        }

        function resolveServerErrorTarget(message) {
            const text = String(message || '');
            const adicional = text.match(/Campo adicional "([^"]+)"/i);
            if (adicional) {
                const field = findFieldByLabelText(adicional[1]);
                return field ? { field: field } : null;
            }
            const campo = text.match(/Campo "([^"]+)"/i);
            if (campo) {
                const field = findFieldByLabelText(campo[1]);
                return field ? { field: field } : null;
            }
            const documento = text.match(/documento "([^"]+)"/i);
            if (documento) {
                const field = findDocumentField(documento[1]);
                if (field) {
                    return { field: field };
                }
                return { section: findDocumentsSection() };
            }

            const rules = [
                [/pelo menos um produto/i, 'section:manual-products-section'],
                [/Nome completo/i, 'nome_completo'],
                [/Nome fantasia/i, 'nome_fantasia'],
                [/Segmento/i, 'segmento'],
                [/Telefone/i, 'telefone'],
                [/e-?mail/i, 'email'],
                [/\bCEP\b/i, 'cep'],
                [/Logradouro/i, 'logradouro'],
                [/Complemento/i, 'complemento'],
                [/N[úu]mero é obrigatório/i, 'numero'],
                [/Bairro/i, 'bairro'],
                [/Cidade/i, 'cidade'],
                [/\bUF\b/i, 'uf'],
                [/CPF do respons[áa]vel/i, 'cpf_pj'],
                [/s[óo]cio adicional/i, 'extra-socio'],
                [/CPF titular/i, 'socios[0][cpf]'],
                [/Data de nascimento do titular/i, 'socios[0][data_nascimento]'],
                [/RG do titular/i, 'socios[0][rg]'],
                [/Data de nascimento/i, 'data_nascimento'],
                [/Data de abertura/i, 'data_abertura'],
                [/Raz[ãa]o social/i, 'razao_social'],
                [/CNPJ/i, 'cnpj'],
                [/\bCPF\b/i, 'cpf'],
                [/Observa[çc][õo]es/i, 'observacoes'],
                [/Valor da ades[ãa]o/i, 'valor_adesao'],
                [/Ades[ãa]o/i, 'adesao'],
                [/\bMDR\b/i, 'mdr'],
                [/Parceiro\/Usu[áa]rio é obrigatório/i, 'owner_ref'],
                [/Parceiro|Usu[áa]rio selecionado/i, 'owner_ref']
            ];

            for (let i = 0; i < rules.length; i++) {
                if (!rules[i][0].test(text)) {
                    continue;
                }
                if (String(rules[i][1]).indexOf('section:') === 0) {
                    return { section: document.getElementById(String(rules[i][1]).slice(8)) };
                }
                if (rules[i][1] === 'extra-socio') {
                    const extraField = formEstabelecimento.querySelector('#socios-extras input, #socios-extras select, #socios-extras textarea');
                    return extraField ? { field: extraField } : null;
                }
                const field = formEstabelecimento.querySelector('[name="' + rules[i][1] + '"]');
                return field ? { field: field } : null;
            }
            return null;
        }

        function applyServerValidationErrors() {
            if (!serverValidationErrors.length) {
                return;
            }
            const targets = [];
            serverValidationErrors.forEach(function(message) {
                const resolved = resolveServerErrorTarget(message);
                if (!resolved) {
                    return;
                }
                if (resolved.section) {
                    showSectionError(resolved.section, message);
                    targets.push(resolved.section);
                    return;
                }
                if (resolved.field) {
                    showFieldError(resolved.field, message);
                    targets.push(fieldWrapper(resolved.field) || resolved.field);
                }
            });
            if (targets.length) {
                scrollToElement(targets[0]);
            }
        }

        formEstabelecimento.addEventListener('input', function(e) {
            const el = e.target;
            if (!el || !el.matches || !el.matches('input, select, textarea') || el.type === 'file') {
                return;
            }
            if (String(el.value || '').trim() !== '') {
                clearFieldError(el);
            }
        });

        formEstabelecimento.addEventListener('change', function(e) {
            const el = e.target;
            if (!el || !el.matches || !el.matches('input, select, textarea')) {
                return;
            }
            if (el.name === 'products[]' || el.name === 'dynamic_products[]') {
                const section = document.getElementById('manual-products-section');
                if (section && formEstabelecimento.querySelector('input[name="products[]"]:checked, input[name="dynamic_products[]"]:checked')) {
                    section.classList.remove('section-invalid');
                    const msg = section.querySelector(':scope > .field-error-msg');
                    if (msg) {
                        msg.remove();
                    }
                }
            }
            if (el.type === 'file') {
                if (el.files && el.files.length) {
                    clearFieldError(el);
                }
                return;
            }
            if (el.type === 'checkbox' || el.type === 'radio' || String(el.value || '').trim() !== '') {
                clearFieldError(el);
            }
        });

        formEstabelecimento.addEventListener('submit', function(e) {
            if (formEstabelecimento.dataset.skipRequired === '1') {
                return;
            }
            if (typeof markRequiredDocumentInputs === 'function') {
                markRequiredDocumentInputs();
            }

            clearAllFieldErrors();
            const invalidFields = collectInvalidFields();
            const productSection = missingProductSection();
            if (!invalidFields.length && !productSection) {
                releaseHiddenRequired();
                return;
            }

            e.preventDefault();
            e.stopImmediatePropagation();

            invalidFields.forEach(function(field) {
                showFieldError(field, requiredFieldMessage(field));
            });
            if (productSection) {
                showSectionError(productSection, 'Selecione pelo menos um produto');
            }

            const target = firstTarget(invalidFields, productSection);
            if (target && target.tagName) {
                scrollToInvalidField(target);
            } else if (productSection) {
                scrollToElement(productSection);
            }
        }, true);

        window.requestAnimationFrame(function() {
            applyServerValidationErrors();
        });
    })();
