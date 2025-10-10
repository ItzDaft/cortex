<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        <h2 class="mb-4 text-center">Enviar Nuevo Resumen</h2>
        <div id="mensaje" class="mb-3"></div>
        <div class="card">
            <div class="card-body">
                <form id="resumenForm">
                    <?php CSRFHelper::getTokenInput(); ?>
                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título del Trabajo:</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" required>
                    </div>

                    <div class="mb-3">
                        <label for="autor_principal" class="form-label">Autor Principal / Ponente:</label>
                        <input type="text" class="form-control" id="autor_principal" name="autor_principal" 
                               value="<?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="coautores" class="form-label">Coautores (opcional):</label>
                        <textarea class="form-control" id="coautores" name="coautores" rows="2"></textarea>
                        <div class="form-text">Escribe un nombre por línea.</div>
                    </div>
                    <div class="mb-3">
                        <label for="adscripcion1" class="form-label">Adscripción 1:</label>
                        <input type="text" class="form-control" id="adscripcion1" name="adscripcion1" required>
                    </div>
                    <div class="mb-3">
                        <label for="adscripcion2" class="form-label">Adscripción 2 (opcional):</label>
                        <input type="text" class="form-control" id="adscripcion2" name="adscripcion2">
                    </div>

                    <div class="mb-3">
                        <label for="area_id" class="form-label">Área Temática:</label>
                        <select class="form-select" id="area_id" name="area_id" required>
                            <option value="">-- Seleccione un área --</option>
                            <?php foreach ($areas as $area): ?>
                                <option value="<?php echo $area['id']; ?>"><?php echo htmlspecialchars($area['nombre_area']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="resumen_texto" class="form-label">Resumen:</label>
                        <textarea class="form-control" id="resumen_texto" name="resumen_texto" rows="10" required maxlength="1500"></textarea>
                        
                        <div id="char-counter" class="form-text text-end">0 / 1500</div>
                    </div>
                    <div class="mb-3">
                        <label for="keyword-input" class="form-label">Palabras Clave:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="keyword-input" placeholder="Escribe una palabra y presiona Enter o Añadir">
                            <button class="btn btn-outline-secondary" type="button" id="add-keyword-btn">Añadir</button>
                        </div>
                        <div class="form-text">Añade de 3 a 5 palabras clave.</div>
                        <div id="keywords-container" class="mt-2">
                            </div>
                        <input type="hidden" id="palabras_clave" name="palabras_clave">
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Enviar para Revisión</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const resumenTextarea = document.getElementById('resumen_texto');
    const charCounter = document.getElementById('char-counter');
    const maxLength = 1500;
    const csrfToken = '<?php echo $_SESSION["csrf_token"]; ?>';
    const baseUrl = '<?php echo BASE_URL; ?>';
    charCounter.textContent = `${resumenTextarea.value.length} / ${maxLength}`;
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
keywordInput.addEventListener('keypress', function(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        addKeyword();
    }
});

keywordsContainer.addEventListener('click', function(event) {
    if (event.target.classList.contains('btn-close')) {
        const index = event.target.getAttribute('data-index');
        keywords.splice(index, 1);
        renderKeywords();
    }
});
renderKeywords();
    resumenTextarea.addEventListener('input', function() {
        const currentLength = this.value.length;
        charCounter.textContent = `${currentLength} / ${maxLength}`;
        
        if (currentLength > maxLength) {
            charCounter.classList.add('text-danger');
        } else {
            charCounter.classList.remove('text-danger');
        }
    });

    document.getElementById('resumenForm').addEventListener('submit', function(event) {
        event.preventDefault();
        const mensajeDiv = document.getElementById('mensaje');
        
        const datos = {
            titulo: this.titulo.value,
            autor_principal: this.autor_principal.value,
            coautores: this.coautores.value,
            area_id: this.area_id.value,
            resumen_texto: this.resumen_texto.value,
            palabras_clave: this.palabras_clave.value,
            adscripcion1: this.adscripcion1.value,
            adscripcion2: this.adscripcion2.value,
            csrf_token: csrfToken
        };

        fetch(`${baseUrl}resumen/crear`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                mensajeDiv.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
            } else {
                window.location.href = `${baseUrl}resumen/misResumenes`;
            }
        })
        .catch(error => console.error('Error:', error));
    });
</script>