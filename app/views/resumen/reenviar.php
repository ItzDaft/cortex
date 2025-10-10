<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        <h2 class="mb-4 text-center">Corregir y Reenviar Resumen</h2>
        <div id="mensaje" class="mb-3"></div>
        <div class="card">
            <div class="card-body">
                <form id="reenvioForm">
                    <?php CSRFHelper::getTokenInput(); ?>
                    <div class="mb-3"><label for="titulo" class="form-label">Título:</label><input type="text" class="form-control" id="titulo" value="<?php echo htmlspecialchars($resumen['titulo']); ?>" required></div>
                    <div class="mb-3"><label for="autor_principal" class="form-label">Autor Principal:</label><input type="text" class="form-control" id="autor_principal" value="<?php echo htmlspecialchars($resumen['autor_principal']); ?>" required></div>
                    <div class="mb-3"><label for="coautores" class="form-label">Coautores:</label><textarea class="form-control" id="coautores" rows="2"><?php echo htmlspecialchars($resumen['coautores']); ?></textarea></div>
                    
                    <div class="mb-3">
                        <label for="adscripcion1" class="form-label">Adscripción 1 (Institución):</label>
                        <input type="text" class="form-control" id="adscripcion1" value="<?php echo htmlspecialchars($resumen['adscripcion1'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="adscripcion2" class="form-label">Adscripción 2 (Institución, opcional):</label>
                        <input type="text" class="form-control" id="adscripcion2" value="<?php echo htmlspecialchars($resumen['adscripcion2'] ?? ''); ?>">
                    </div>

                    <div class="mb-3"><label for="area_id" class="form-label">Área Temática:</label>
                        <select class="form-select" id="area_id" required>
                            <?php foreach ($areas as $area): ?><option value="<?php echo $area['id']; ?>" <?php echo ($area['id'] == $resumen['area_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($area['nombre_area']); ?></option><?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="keyword-input" class="form-label">Palabras Clave:</label>
                        <div class="input-group"><input type="text" class="form-control" id="keyword-input" placeholder="Escribe una palabra y presiona Enter o Añadir"><button class="btn btn-outline-secondary" type="button" id="add-keyword-btn">Añadir</button></div>
                        <div class="form-text">Añade de 3 a 5 palabras clave.</div>
                        <div id="keywords-container" class="mt-2"></div>
                        <input type="hidden" id="palabras_clave" name="palabras_clave" value="<?php echo htmlspecialchars($resumen['palabras_clave'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="resumen_texto" class="form-label">Resumen:</label>
                        <textarea class="form-control" id="resumen_texto" rows="10" required maxlength="1500"><?php echo htmlspecialchars($resumen['resumen_texto']); ?></textarea>
                        <div id="char-counter" class="form-text text-end">0 / 1500</div>
                    </div>
                    <div class="d-grid"><button type="submit" id="submitBtn" class="btn btn-primary">Reenviar para Evaluación</button></div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const baseUrl = '<?php echo BASE_URL; ?>';
    const csrfToken = '<?php echo $_SESSION["csrf_token"] ?? ""; ?>';
    const resumenId = <?php echo $view_resumen['id']; ?>;

    const resumenTextarea = document.getElementById('resumen_texto');
    const charCounter = document.getElementById('char-counter');
    const maxLength = 1500;
    function updateCharCounter() {
        charCounter.textContent = `${resumenTextarea.value.length} / ${maxLength}`;
        charCounter.classList.toggle('text-danger', resumenTextarea.value.length > maxLength);
    }
    resumenTextarea.addEventListener('input', updateCharCounter);
    updateCharCounter(); 

    const keywordInput = document.getElementById('keyword-input');
    const addKeywordBtn = document.getElementById('add-keyword-btn');
    const keywordsContainer = document.getElementById('keywords-container');
    const hiddenKeywordsInput = document.getElementById('palabras_clave');
    let keywords = hiddenKeywordsInput.value ? hiddenKeywordsInput.value.split(',').filter(k => k) : [];

    function renderKeywords() {
        keywordsContainer.innerHTML = '';
        keywords.forEach((keyword, index) => {
            const badge = document.createElement('span');
            badge.className = 'badge bg-primary me-2 mb-2 p-2';
            badge.textContent = keyword;
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn-close btn-close-white ms-2';
            removeBtn.setAttribute('data-index', index);
            badge.appendChild(removeBtn);
            keywordsContainer.appendChild(badge);
        });
        hiddenKeywordsInput.value = keywords.join(',');
    }
    function addKeyword() {
        const keyword = keywordInput.value.trim();
        if (keyword && !keywords.includes(keyword)) {
            keywords.push(keyword);
            keywordInput.value = '';
            renderKeywords();
        }
    }
    addKeywordBtn.addEventListener('click', addKeyword);
    keywordInput.addEventListener('keypress', e => e.key === 'Enter' && (e.preventDefault(), addKeyword()));
    keywordsContainer.addEventListener('click', e => {
        if (e.target.classList.contains('btn-close')) {
            keywords.splice(e.target.getAttribute('data-index'), 1);
            renderKeywords();
        }
    });
    renderKeywords(); 

    document.getElementById('reenvioForm').addEventListener('submit', function(event) {
        event.preventDefault();
        const submitBtn = document.getElementById('submitBtn');
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Reenviando...`;

        const datos = {
            titulo: document.getElementById('titulo').value,
            autor_principal: document.getElementById('autor_principal').value,
            coautores: document.getElementById('coautores').value,
            adscripcion1: document.getElementById('adscripcion1').value,
            adscripcion2: document.getElementById('adscripcion2').value,
            area_id: document.getElementById('area_id').value,
            palabras_clave: hiddenKeywordsInput.value,
            resumen_texto: resumenTextarea.value,
            csrf_token: csrfToken
        };
        fetch(`${baseUrl}resumen/procesarReenvio/${resumenId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        })
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                document.getElementById('mensaje').innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
            } else {
                window.location.href = `${baseUrl}resumen/misResumenes`;
            }
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        });
    });
</script>