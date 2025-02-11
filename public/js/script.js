// Estado global da aplicação
// Armazena a página atual para paginação
let currentPage = 1;

// Número de itens exibidos por página
const itemsPerPage = 10;

// Termo de busca atual
let searchTerm = '';

// Evento disparado quando o DOM está completamente carregado
document.addEventListener('DOMContentLoaded', function() {
    // Carregar lista inicial de usuários
    loadUsers();

    // Configurar formulário de busca
    const searchForm = document.getElementById('search-form');
    const searchInput = document.getElementById('search-input');

    // Verificar se elementos críticos de busca existem
    if (!searchForm || !searchInput) {
        // Lançar erro se elementos não forem encontrados
        throw new Error('Critical DOM elements not found: ' + 
            (!searchForm ? 'search-form ' : '') + 
            (!searchInput ? 'search-input' : '')
        );
        return;
    }

    // Remover o evento de submit padrão do formulário
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
    });

    // Implementar busca em tempo real com debounce
    let searchTimeout;
    searchInput.addEventListener('input', function(e) {
        // Limpar timeout anterior para evitar múltiplas chamadas
        clearTimeout(searchTimeout);

        // Definir novo timeout para reduzir chamadas desnecessárias
        searchTimeout = setTimeout(() => {
            // Atualizar termo de busca, removendo espaços em branco
            searchTerm = this.value.trim();
            
            // Resetar para primeira página
            currentPage = 1;

            // Adicionar feedback visual durante a busca
            const tableBody = document.querySelector('tbody');
            const noResultsElement = document.querySelector('.alert.alert-info');
            
            if (tableBody) {
                // Exibir spinner de carregamento
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Buscando...</span>
                            </div>
                            Buscando resultados...
                        </td>
                    </tr>
                `;
            }

            // Ocultar mensagem de sem resultados durante a busca
            if (noResultsElement) {
                noResultsElement.style.display = 'none';
            }

            // Carregar usuários com o novo termo de busca
            loadUsers();
        }, 300); // Delay de 300ms para reduzir chamadas
    });
});

/**
 * Carrega lista de usuários do servidor
 * Função assíncrona que busca usuários com suporte a paginação e pesquisa
 */
async function loadUsers() {
    try {
        // Verificar elementos críticos do DOM
        const noResultsElement = document.querySelector('.alert.alert-info');
        const usersTableBodyElement = document.querySelector('tbody');
        const paginationControlsElement = document.querySelector('.pagination');

        // Validar elementos necessários
        const missingElements = [];
        if (!usersTableBodyElement) missingElements.push('tbody');
        if (!paginationControlsElement) missingElements.push('pagination');
        
        // Lançar erro se elementos críticos estiverem ausentes
        if (missingElements.length > 0) {
            throw new Error('Critical DOM elements not found: ' + missingElements.join(' '));
        }

        // Realizar requisição para API de listagem de usuários
        const response = await fetch(`api.php?action=list&pagina=${currentPage}&itensPorPagina=${itemsPerPage}&busca=${encodeURIComponent(searchTerm)}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        // Processar resposta da API
        const data = await response.json();

        if (data.success) {
            // Verificar se não há usuários encontrados
            if (data.data.usuarios.length === 0) {
                // Exibir mensagem de nenhum resultado
                if (noResultsElement) {
                    noResultsElement.style.display = 'block';
                }
                
                // Limpar corpo da tabela e controles de paginação
                usersTableBodyElement.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center text-danger">
                            Registro não encontrado
                        </td>
                    </tr>
                `;
                paginationControlsElement.innerHTML = '';
            } else {
                // Renderizar usuários na tabela
                usersTableBodyElement.innerHTML = data.data.usuarios.map(user => `
                    <tr>
                        <td>${escapeHtml(user.nome || 'N/A')}</td>
                        <td>${escapeHtml(user.email || 'N/A')}</td>
                        <td>
                            <button onclick="openModal('view', ${user.id})" class="btn btn-info">Visualizar</button>
                            <button onclick="openModal('edit', ${user.id})" class="btn btn-warning">Editar</button>
                            <button onclick="confirmDelete(${user.id})" class="btn btn-danger">Excluir</button>
                        </td>
                    </tr>
                `).join('');

                // Atualizar controles de paginação
                const { paginaAtual, totalPaginas } = data.data.paginacao;
                let paginationHtml = '';

                // Adicionar botão de página anterior
                if (paginaAtual > 1) {
                    paginationHtml += `
                        <a href="?pagina=${paginaAtual - 1}&busca=${encodeURIComponent(searchTerm)}" class="btn btn-secondary">
                            Anterior
                        </a>
                    `;
                }

                // Adicionar informação de página atual
                paginationHtml += `<span>Página ${paginaAtual} de ${totalPaginas}</span>`;

                // Adicionar botão de próxima página
                if (paginaAtual < totalPaginas) {
                    paginationHtml += `
                        <a href="?pagina=${paginaAtual + 1}&busca=${encodeURIComponent(searchTerm)}" class="btn btn-secondary">
                            Próxima
                        </a>
                    `;
                }

                // Atualizar controles de paginação
                paginationControlsElement.innerHTML = paginationHtml;

                // Ocultar mensagem de sem resultados
                if (noResultsElement) {
                    noResultsElement.style.display = 'none';
                }
            }
        } else {
            // Lançar erro se a requisição não for bem-sucedida
            throw new Error(data.message || 'Erro desconhecido ao carregar usuários');
        }
    } catch (error) {
        // Tratamento de erros na requisição
        const noResultsElement = document.querySelector('.alert.alert-info');
        const usersTableBodyElement = document.querySelector('tbody');
        const paginationControlsElement = document.querySelector('.pagination');

        // Exibir mensagem de erro
        if (noResultsElement) noResultsElement.style.display = 'block';
        if (usersTableBodyElement) usersTableBodyElement.innerHTML = '';
        if (paginationControlsElement) paginationControlsElement.innerHTML = '';

        // Usar SweetAlert para exibir mensagem de erro detalhada
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Não foi possível carregar os usuários.',
            footer: `<pre>${error.message}</pre>`
        });
    }
}

// Função para renderizar usuários na tabela
function renderUsers(users) {
    // Selecionar corpo da tabela
    const tbody = document.querySelector('tbody');
    if (!tbody) {
        throw new Error('tbody element not found');
    }

    // Verificar se os dados dos usuários são válidos
    if (!Array.isArray(users)) {
        throw new Error('Invalid users data:', users);
    }

    // Renderizar usuários na tabela
    tbody.innerHTML = users.map(user => `
        <tr>
            <td>${escapeHtml(user.nome || 'N/A')}</td>
            <td>${escapeHtml(user.email || 'N/A')}</td>
            <td>
                <button onclick="openModal('view', ${user.id})" class="btn btn-info">Visualizar</button>
                <button onclick="openModal('edit', ${user.id})" class="btn btn-warning">Editar</button>
                <button onclick="confirmDelete(${user.id})" class="btn btn-danger">Excluir</button>
            </td>
        </tr>
    `).join('');
}

// Função para renderizar controles de paginação
function renderPagination(paginacao) {
    // Selecionar controles de paginação
    const controls = document.querySelector('.pagination');
    if (!controls) {
        throw new Error('pagination element not found');
    }

    // Extrair informações de paginação
    const { paginaAtual, totalPaginas } = paginacao;

    // Renderizar controles de paginação
    let html = '';
    
    // Adicionar botão de página anterior
    if (paginaAtual > 1) {
        html += `<button onclick="changePage(${paginaAtual - 1})" class="btn btn-secondary">Anterior</button>`;
    }

    // Adicionar informação de página atual
    html += `<span>Página ${paginaAtual} de ${totalPaginas}</span>`;

    // Adicionar botão de próxima página
    if (paginaAtual < totalPaginas) {
        html += `<button onclick="changePage(${paginaAtual + 1})" class="btn btn-secondary">Próxima</button>`;
    }

    // Atualizar controles de paginação
    controls.innerHTML = html;
}

// Função para mudar de página
function changePage(page) {
    // Atualizar página atual
    currentPage = page;
    
    // Carregar usuários para a nova página
    loadUsers();
}

// Função para abrir modal
async function openModal(type, id = null) {
    try {
        // Selecionar modal e seus elementos
        const modal = document.getElementById('modal');
        const modalBody = document.getElementById('modal-body');
        const modalTitle = document.getElementById('modal-title');

        // Verificar se elementos do modal existem
        if (!modal || !modalBody || !modalTitle) {
            throw new Error('Modal elements not found');
        }

        // Limpar corpo do modal
        modalBody.innerHTML = '';
        
        // Definir título do modal
        switch(type) {
            case 'create':
                // Título para criar novo usuário
                modalTitle.textContent = 'Novo Usuário';
                renderForm();
                break;
            case 'edit':
                // Título para editar usuário
                modalTitle.textContent = 'Editar Usuário';
                await loadAndRenderForm(id);
                break;
            case 'view':
                // Título para visualizar usuário
                modalTitle.textContent = 'Detalhes do Usuário';
                await loadAndRenderDetails(id);
                break;
            default:
                // Lançar erro para tipo de modal inválido
                throw new Error('Invalid modal type');
        }

        // Exibir modal
        modal.classList.add('modal-show');
    } catch (error) {
        // Exibir mensagem de erro
        Swal.fire('Erro!', 'Não foi possível abrir o modal.', 'error');
    }
}

// Função para renderizar formulário
function renderForm(userData = null) {
    // Selecionar corpo do modal
    const modalBody = document.getElementById('modal-body');
    if (!modalBody) {
        throw new Error('modal-body element not found');
    }

    // Renderizar formulário
    modalBody.innerHTML = `
        <form id="userForm" onsubmit="submitForm(event)">
            ${userData ? `<input type="hidden" name="id" value="${userData.id}">` : ''}
            <div class="form-group">
                <label>Nome</label>
                <input type="text" name="nome" class="form-control" value="${userData?.nome || ''}" required minlength="2">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="${userData?.email || ''}" required>
            </div>
            <div class="form-group">
                <label>Telefone</label>
                <input type="tel" name="telefone" class="form-control" 
                    placeholder="(99) 99999-9999"
                    oninput="formatarTelefone(this)"
                    value="${userData?.telefone || ''}"
                    required
                    pattern="\\(\\d{2}\\) \\d{4,5}-\\d{4}">
            </div>
            <div class="form-group">
                <label>Data de Nascimento</label>
                <input type="date" name="data_nascimento" class="form-control" 
                    value="${userData?.data_nascimento ? formatDate(userData.data_nascimento) : ''}" 
                    required
                    max="${new Date().toISOString().split('T')[0]}"
                    min="1900-01-01">
            </div>
            <button type="submit" class="btn btn-primary">${userData ? 'Atualizar' : 'Cadastrar'}</button>
        </form>
    `;
}

// Função para carregar e renderizar formulário de edição
async function loadAndRenderForm(id) {
    try {
        // Realizar requisição para API de visualização de usuário
        const response = await fetch(`api.php?action=view&id=${id}`);
        const data = await response.json();

        // Verificar se a requisição foi bem-sucedida
        if (data.success) {
            // Renderizar formulário com dados do usuário
            renderForm(data.data);
        } else {
            // Lançar erro se a requisição não for bem-sucedida
            throw new Error(data.message || 'Erro ao carregar dados do usuário');
        }
    } catch (error) {
        // Exibir mensagem de erro
        Swal.fire('Erro!', 'Não foi possível carregar os dados do usuário.', 'error');
    }
}

// Função para carregar e renderizar detalhes do usuário
async function loadAndRenderDetails(id) {
    try {
        // Realizar requisição para API de visualização de usuário
        const response = await fetch(`api.php?action=view&id=${id}`);
        const data = await response.json();

        // Verificar se a requisição foi bem-sucedida
        if (data.success) {
            // Selecionar corpo do modal
            const modalBody = document.getElementById('modal-body');
            if (!modalBody) {
                throw new Error('modal-body element not found');
            }

            // Renderizar detalhes do usuário
            const user = data.data;
            modalBody.innerHTML = `
                <div class="user-details">
                    <div class="form-group">
                        <strong>Informações Pessoais</strong>
                        <hr>
                        <p><strong>Nome:</strong> ${escapeHtml(user.nome)}</p>
                        <p><strong>Email:</strong> ${escapeHtml(user.email)}</p>
                        <p><strong>Telefone:</strong> ${escapeHtml(user.telefone || 'N/A')}</p>
                        <p><strong>Data de Nascimento:</strong> ${user.data_nascimento['date'] ? formatDateDisplay(user.data_nascimento['date']) : 'N/A'}</p>
                    </div>
                </div>
            `;
        } else {
            // Lançar erro se a requisição não for bem-sucedida
            throw new Error(data.message || 'Erro ao carregar detalhes do usuário');
        }
    } catch (error) {
        // Exibir mensagem de erro
        Swal.fire('Erro!', 'Não foi possível carregar os detalhes do usuário.', 'error');
    }
}

// Função para fechar modal
function closeModal() {
    // Selecionar modal
    const modal = document.getElementById('modal');
    if (!modal) {
        throw new Error('modal element not found');
    }

    // Ocultar modal
    modal.classList.remove('modal-show');
}

// Função para confirmar exclusão
function confirmDelete(id) {
    // Exibir mensagem de confirmação
    Swal.fire({
        title: 'Tem certeza?',
        text: 'Você não poderá reverter esta ação!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then(async (result) => {
        // Verificar se a exclusão foi confirmada
        if (result.isConfirmed) {
            try {
                // Realizar requisição para API de exclusão de usuário
                const response = await fetch(`api.php?action=delete&id=${id}`);
                const data = await response.json();

                // Verificar se a requisição foi bem-sucedida
                if (data.success) {
                    // Exibir mensagem de sucesso
                    await Swal.fire('Excluído!', 'O usuário foi excluído com sucesso.', 'success');
                    // Carregar usuários novamente
                    loadUsers();
                } else {
                    // Lançar erro se a requisição não for bem-sucedida
                    throw new Error(data.message || 'Erro ao excluir usuário');
                }
            } catch (error) {
                // Exibir mensagem de erro
                Swal.fire('Erro!', 'Não foi possível excluir o usuário.', 'error');
            }
        }
    });
}

// Função para submeter formulário
async function submitForm(event) {
    // Prevenir envio do formulário
    event.preventDefault();
    
    // Selecionar formulário
    const form = event.target;
    const formData = new FormData(form);
    const id = formData.get('id');

    try {
        // Definir ação para o formulário
        const action = id ? 'update' : 'create';
        
        // Validar campos obrigatórios
        const formDataJson = Object.fromEntries(formData.entries());
        const requiredFields = ['nome', 'email', 'telefone', 'data_nascimento'];
        const missingFields = requiredFields.filter(field => !formDataJson[field]);
        
        // Lançar erro se campos obrigatórios estiverem ausentes
        if (missingFields.length > 0) {
            throw new Error(`Campos obrigatórios não preenchidos: ${missingFields.join(', ')}`);
        }

        // Formatatar data de nascimento
        if (formDataJson.data_nascimento) {
            formDataJson.data_nascimento = formatDate(formDataJson.data_nascimento);
        }

        // Realizar requisição para API de criação ou atualização de usuário
        const response = await fetch(`api.php?action=${action}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formDataJson)
        });

        // Processar resposta da API
        const responseText = await response.text();

        // Parsear resposta como JSON
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            // Lançar erro se a resposta não for um JSON válido
            throw new Error('Resposta inválida do servidor: ' + responseText);
        }

        // Verificar se a requisição foi bem-sucedida
        if (response.ok && data.success) {
            // Exibir mensagem de sucesso
            await Swal.fire('Sucesso!', data.message || 'Operação realizada com sucesso', 'success');
            // Fechar modal
            closeModal();
            // Carregar usuários novamente
            loadUsers();
        } else {
            // Exibir mensagem de erro
            let mensagemErro = data.message || `Erro ao ${id ? 'atualizar' : 'criar'} usuário`;
            
            await Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: mensagemErro
            });
        }
    } catch (error) {
        // Exibir mensagem de erro
        Swal.fire({
            icon: 'error', 
            title: 'Erro!', 
            text: error.message
        });
    }
}

// Função para formatar telefone
function formatarTelefone(input) {
    // Extrair telefone sem máscara
    let telefone = input.value.replace(/\D/g, '');
    
    // Limitar telefone a 11 dígitos
    if (telefone.length > 11) {
        telefone = telefone.slice(0, 11);
    }
    
    // Adicionar máscara de telefone
    if (telefone.length > 10) {
        telefone = telefone.replace(/^(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
    } else if (telefone.length > 6) {
        telefone = telefone.replace(/^(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
    } else if (telefone.length > 2) {
        telefone = telefone.replace(/^(\d{2})(\d+)/, '($1) $2');
    }
    
    // Atualizar valor do telefone
    input.value = telefone;
}

// Função para formatar data
function formatDate(dateInput) {
    try {
        // Verificar se a data é um objeto
        if (typeof dateInput === 'object' && dateInput !== null) {
            // Extrair data do objeto
            if (dateInput.date) {
                dateInput = dateInput.date;
            } else if (dateInput.toString) {
                dateInput = dateInput.toString();
            } else {
                return '';
            }
        }

        // Verificar se a data é válida
        if (!dateInput) return '';
        
        // Limpar data
        const cleanDateString = dateInput.split('.')[0].replace(' ', 'T');

        // Criar data
        const date = new Date(cleanDateString);
        
        // Calcular offset de fuso horário
        const brasiliaOffset = -3 * 60;
        const localOffset = date.getTimezoneOffset();
        const adjustedDate = new Date(date.getTime() + (brasiliaOffset - localOffset) * 60000);
        
        // Verificar se a data é válida
        if (isNaN(adjustedDate.getTime())) {
            return '';
        }
        
        // Extrair ano, mês e dia
        const year = adjustedDate.getFullYear();
        const month = String(adjustedDate.getMonth() + 1).padStart(2, '0');
        const day = String(adjustedDate.getDate()).padStart(2, '0');
        
        // Retornar data formatada
        return `${year}-${month}-${day}`;
    } catch (error) {
        return '';
    }
}

// Função para formatar data para exibição local
function formatDateDisplay(dateInput) {
    try {
        // Verificar se a data é um objeto
        if (typeof dateInput === 'object' && dateInput !== null) {
            // Extrair data do objeto
            if (dateInput.date) {
                dateInput = dateInput.date;
            } else if (dateInput.toString) {
                dateInput = dateInput.toString();
            } else {
                return 'N/A';
            }
        }

        // Verificar se a data é válida
        if (!dateInput) return 'N/A';
        
        // Limpar data
        const cleanDateString = dateInput.split('.')[0].replace(' ', 'T');

        // Criar data
        const date = new Date(cleanDateString);
        
        // Calcular offset de fuso horário
        const brasiliaOffset = -3 * 60;
        const localOffset = date.getTimezoneOffset();
        const adjustedDate = new Date(date.getTime() + (brasiliaOffset - localOffset) * 60000);
        
        // Verificar se a data é válida
        if (isNaN(adjustedDate.getTime())) {
            return 'N/A';
        }
        
        // Extrair dia, mês e ano
        const day = String(adjustedDate.getDate()).padStart(2, '0');
        const month = String(adjustedDate.getMonth() + 1).padStart(2, '0');
        const year = adjustedDate.getFullYear();
        
        // Retornar data formatada
        return `${day}/${month}/${year}`;
    } catch (error) {
        return 'N/A';
    }
}

// Função para escapar HTML
function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Fechar modal ao clicar fora
window.onclick = function(event) {
    // Selecionar modal
    const modal = document.getElementById('modal');
    if (event.target == modal) {
        // Fechar modal
        closeModal();
    }
}