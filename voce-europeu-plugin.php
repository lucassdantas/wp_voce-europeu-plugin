<?php
/**
 * Plugin Name: Você europeu plugin
 * Description: Alterna o nome do formulário entre Pedro e Fernando a cada envio.
 * Version: 1.1
 * Author: RD Exclusive
 */

if (!defined('ABSPATH')) {
    exit; // Evita acesso direto
}

// Criar tabela ao ativar o plugin
function create_seller_toggle_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'seller_form_toggle';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT PRIMARY KEY AUTO_INCREMENT,
        toggle_value TINYINT(1) NOT NULL DEFAULT 0
    ) $charset_collate;";
    
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
    
    // Verifica se já há um valor na tabela, se não, insere
    if (!$wpdb->get_var("SELECT COUNT(*) FROM $table_name")) {
        $wpdb->insert($table_name, ['toggle_value' => 0]);
    }
}
register_activation_hook(__FILE__, 'create_seller_toggle_table');

// Função para alternar vendedor
function select_seller_by_seller_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'seller_form_toggle';
    
    $current_value = (int) $wpdb->get_var("SELECT toggle_value FROM $table_name LIMIT 1");
    
    $seller = ($current_value === 0) ? 'Pedro' : 'Fernando';
    
    // Alterna o valor no banco de dados
    $new_value = ($current_value === 0) ? 1 : 0;
    $wpdb->update($table_name, ['toggle_value' => $new_value], ['id' => 1]);
    
    return $seller;
}


function seller_form_shortcode() {
  ob_start();
  ?>
<style>
      #sellerForm {
          color: var(--e-global-color-44fc3f2);
          display: flex;
          flex-direction: column;
          gap: 10px;
          width:100%;
          margin: auto;
      }
      #sellerForm .row {
          display: flex;
          gap: 10px;
      }
      #sellerForm .row div {
          flex: 1;
      }
      #sellerForm input, #sellerForm select {
          width: 100%;
          padding: 10px;
          border-radius: 12px;
          border: 1px solid var(--e-global-color-44fc3f2);
      }
      #sellerForm button {
          color: #fff;
          background-color: #FFC62B;
          border: unset;
          border-radius: 12px;
          width: 100%;
          padding: 10px;
          font-size: 16px;
          cursor: pointer;
      }
      @media (max-width: 600px) {
          #sellerForm .row {
              flex-direction: column;
          }
      }
  </style>
  <form id='sellerForm' method="post" action="">
      <label for="service">Selecione aqui o serviço do seu interesse:</label>
      <select name="service" required>
          <option value="Busca da Certidão de Nascimento do Espanhol">Busca da Certidão de Nascimento do Espanhol</option>
          <option value="Assessoria completa para Cidadania Espanhola">Assessoria completa para Cidadania Espanhola (Apenas 100 vagas)</option>
          <option value="Pacote Faça Você Mesmo">Pacote Faça Você Mesmo</option>
      </select>
      <div class="row">
          <div>
              <label for="name">Nome:</label>
              <input type="text" name="name" placeholder='Nome' required>
          </div>
          <div>
              <label for="email">Email:</label>
              <input type="email" name="email" placeholder='Email' required>
          </div>
      </div>
      <label for="phone">Telefone:</label>
      <input type="text" id='phone' name="phone" placeholder='(__) ____-____' required>
      <input type="hidden" name="utm_source" value="<?php echo $_GET['utm_source'] ?? ''; ?>">
      <input type="hidden" name="utm_medium" value="<?php echo $_GET['utm_medium'] ?? ''; ?>">
      <input type="hidden" name="utm_campaign" value="<?php echo $_GET['utm_campaign'] ?? ''; ?>">
      <input type="hidden" name="utm_content" value="<?php echo $_GET['utm_content'] ?? ''; ?>">
      <input type="hidden" name="utm_term" value="<?php echo $_GET['utm_term'] ?? ''; ?>">
      <input type="hidden" name="seller" value="">
      <button type="submit" name="submit_seller_form">Agendar Reunião</button>
  </form>
  <?php
  return ob_get_clean();
}
add_shortcode('seller_form', 'seller_form_shortcode');

// Função para processar o formulário
function process_seller_form() {
  if (isset($_POST['submit_seller_form'])) {
      $seller = select_seller_by_seller_table();
      
      $to = 'lucasdantas.rdmarketingdigital@gmail.com';
      $subject = 'Novo Lead do Formulário';
      $message = "Serviço: {$_POST['service']}\n";
      $message .= "Nome: {$_POST['name']}\n";
      $message .= "Telefone: {$_POST['phone']}\n";
      $message .= "Email: {$_POST['email']}\n";
      $message .= "UTM Source: {$_POST['utm_source']}\n";
      $message .= "UTM Medium: {$_POST['utm_medium']}\n";
      $message .= "UTM Campaign: {$_POST['utm_campaign']}\n";
      $message .= "UTM Content: {$_POST['utm_content']}\n";
      $message .= "UTM Term: {$_POST['utm_term']}\n";
      $message .= "Vendedor: $seller\n";
      
      $headers = ['Content-Type: text/plain; charset=UTF-8'];
      
      wp_mail($to, $subject, $message, $headers);
  }
}
add_action('init', 'process_seller_form');