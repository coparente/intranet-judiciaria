# Chat - Troubleshooting e Soluções

## Problema Resolvido: Templates não carregando

### ❌ Erro Original
```
Erro ao carregar templates: Unexpected token '<', "<!DOCTYPE "... is not valid JSON
```

### 🔍 Diagnóstico
O erro ocorria porque:
1. **Requisições AJAX sem credenciais**: O JavaScript não estava enviando os cookies de sessão
2. **Redirecionamento para login**: Usuários não autenticados eram redirecionados para página HTML de login
3. **Resposta HTML ao invés de JSON**: O sistema retornava HTML da página de login ao invés de JSON

### ✅ Solução Implementada

#### 1. **Correção no JavaScript** (`app/Views/chat/templates.php`)
```javascript
fetch('<?= URL ?>/chat/gerenciarTemplates', {
    method: 'POST',
    body: formData,
    credentials: 'same-origin',  // ✅ INCLUIR cookies de sessão
    headers: {
        'X-Requested-With': 'XMLHttpRequest'  // ✅ Identificar como AJAX
    }
})
```

#### 2. **Melhoria no Controller** (`app/Controllers/Chat.php`)
```php
// Detectar se é uma requisição AJAX
$isAjax = $_SERVER['REQUEST_METHOD'] == 'POST' || 
         isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
         strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Verificar autenticação
if (!isset($_SESSION['usuario_id'])) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 401, 'error' => 'Usuário não autenticado']);
        return;
    }
    Helper::redirecionar('usuarios/login');
    return;
}
```

#### 3. **Validação de Content-Type**
```javascript
const contentType = response.headers.get('content-type');
if (!contentType || !contentType.includes('application/json')) {
    throw new Error('Resposta não é JSON válido - Content-Type: ' + contentType);
}
```

### 📊 Resultado
- ✅ **JSON válido**: Resposta sempre em formato JSON
- ✅ **Sessão mantida**: Cookies incluídos nas requisições AJAX
- ✅ **Erro tratado**: Mensagens de erro apropriadas para cada situação
- ✅ **12 templates carregados**: API SERPRO funcionando corretamente

### 🔧 Outras Melhorias Implementadas

#### Tratamento de Erros Específicos
```javascript
if (error.message.includes('Content-Type')) {
    alert('Erro de sessão. Atualize a página e faça login novamente.');
} else {
    alert('Erro ao carregar templates: ' + error.message);
}
```

#### Códigos de Status HTTP Apropriados
- **200**: Sucesso
- **401**: Não autenticado
- **403**: Sem permissão
- **500**: Erro interno

### 🧪 Como Testar

1. **Faça login** com perfil `admin`
2. **Acesse** `/chat/configuracoes` → "Gerenciar Templates"
3. **Verifique** se os templates carregam automaticamente
4. **Teste** criação e exclusão de templates

### 🏗️ Arquivos Modificados

1. **`app/Views/chat/templates.php`**
   - ✅ Adicionado `credentials: 'same-origin'`
   - ✅ Adicionado header `X-Requested-With`
   - ✅ Validação de Content-Type
   - ✅ Tratamento de erros específicos

2. **`app/Controllers/Chat.php`**
   - ✅ Detecção de requisições AJAX
   - ✅ Tratamento de usuários não autenticados
   - ✅ Headers JSON apropriados
   - ✅ Códigos de status HTTP corretos

### 🎯 Próximos Passos

1. **Aplicar mesma correção** aos outros métodos AJAX (webhooks, QR codes, métricas)
2. **Implementar refresh automático** de token quando expirar
3. **Adicionar logs** para auditoria de uso da API
4. **Criar testes automatizados** para prevenir regressões

---

## 📝 Notas Técnicas

### API SERPRO - Estrutura de Resposta
```json
{
    "status": 200,
    "response": [
        {
            "name": "template_name",
            "status": "APPROVED",
            "components": [...]
        }
    ]
}
```

### Configurações Necessárias
- **Session** ativa e válida
- **Perfil** `admin` para gerenciar templates
- **API SERPRO** configurada e funcionando
- **Cookies** habilitados no navegador

### Troubleshooting Rápido
```bash
# Verificar sintaxe PHP
php -l app/Controllers/Chat.php

# Testar API diretamente
curl -X POST "http://localhost/intranet-judiciaria/chat/gerenciarTemplates" \
     -d "acao=listar" \
     -H "Content-Type: application/x-www-form-urlencoded" \
     --cookie-jar cookies.txt --cookie cookies.txt
``` 