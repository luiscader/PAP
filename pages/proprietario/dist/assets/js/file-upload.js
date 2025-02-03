document.addEventListener('DOMContentLoaded', function() {
    const allowedMimeTypes = [
        'image/jpeg', 
        'image/png', 
        'image/gif'
    ];
    const maxFileSize = 5 * 1024 * 1024; // 5MB em bytes

    function validateFile(file) {
        // Verificação de tipo de arquivo usando MIME type
        if (!allowedMimeTypes.includes(file.type)) {
            alert('Tipo de arquivo não permitido! Apenas JPEG, PNG e GIF são aceitos.');
            return false;
        }

        // Verificação de tamanho
        if (file.size > maxFileSize) {
            alert('O tamanho do arquivo excede 5MB. Por favor, selecione um arquivo menor.');
            return false;
        }

        return true;
    }

    // Seleciona todos os inputs de arquivo
    const fileInputs = document.querySelectorAll('input[type="file"]');

    fileInputs.forEach(input => {
        input.addEventListener('change', function(event) {
            const files = this.files;
            
            // Verifica cada arquivo
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                
                // Se o arquivo não passar na validação, limpa o input
                if (!validateFile(file)) {
                    this.value = ''; // Limpa o input de arquivo
                    return;
                }
            }
        });

        // Adiciona validação no momento do submit do formulário como camada extra de segurança
        const form = input.closest('form');
        if (form) {
            form.addEventListener('submit', function(event) {
                const files = input.files;
                
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    
                    if (!validateFile(file)) {
                        event.preventDefault(); // Impede o envio do formulário
                        return;
                    }
                }
            });
        }
    });
});