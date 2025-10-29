<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pasarela de Pago - MiHieribal</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 50%, #43a047 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
      position: relative;
      overflow-x: hidden;
    }

    body::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-image: 
        radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.08) 0%, transparent 50%);
      pointer-events: none;
    }

    .payment-container {
      background: white;
      border-radius: 24px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      max-width: 900px;
      width: 100%;
      display: grid;
      grid-template-columns: 1fr 1fr;
      overflow: hidden;
      position: relative;
      z-index: 1;
    }

    .payment-left {
      padding: 3rem;
      background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    }

    .payment-right {
      padding: 3rem;
      background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
      display: flex;
      flex-direction: column;
      gap: 1.5rem;
    }

    .logo-section {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-bottom: 2rem;
    }

    .logo-section h1 {
      font-size: 1.8rem;
      font-weight: 800;
      color: #2e7d32;
    }

    .logo-section .emoji {
      font-size: 2rem;
    }

    .section-title {
      font-size: 1.5rem;
      font-weight: 700;
      color: #1a1a1a;
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .form-group {
      margin-bottom: 1.5rem;
    }

    .form-group label {
      display: block;
      font-weight: 600;
      color: #333;
      margin-bottom: 0.5rem;
      font-size: 0.95rem;
    }

    .form-group input,
    .form-group select {
      width: 100%;
      padding: 0.9rem 1.2rem;
      border: 2px solid #e0e0e0;
      border-radius: 12px;
      font-size: 1rem;
      transition: all 0.3s ease;
      background: white;
    }

    .form-group input:focus,
    .form-group select:focus {
      outline: none;
      border-color: #43a047;
      box-shadow: 0 0 0 4px rgba(67, 160, 71, 0.1);
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }

    .card-input {
      position: relative;
    }

    .card-input input {
      padding-right: 3.5rem;
    }

    .card-icon {
      position: absolute;
      right: 1rem;
      top: 50%;
      transform: translateY(-50%);
      font-size: 1.5rem;
    }

    .payment-methods {
      display: flex;
      gap: 1rem;
      margin-bottom: 2rem;
    }

    .method-btn {
      flex: 1;
      padding: 1rem;
      border: 2px solid #e0e0e0;
      border-radius: 12px;
      background: white;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 0.5rem;
      font-weight: 600;
    }

    .method-btn:hover {
      border-color: #43a047;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(67, 160, 71, 0.2);
    }

    .method-btn.active {
      border-color: #43a047;
      background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%);
      color: #2e7d32;
    }

    .method-btn .emoji {
      font-size: 2rem;
    }

    .order-summary {
      background: white;
      padding: 1.5rem;
      border-radius: 16px;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    }

    .order-item {
      display: flex;
      justify-content: space-between;
      padding: 0.8rem 0;
      border-bottom: 1px solid #f0f0f0;
    }

    .order-item:last-child {
      border-bottom: none;
    }

    .order-item-name {
      font-weight: 600;
      color: #333;
    }

    .order-item-price {
      font-weight: 700;
      color: #43a047;
    }

    .order-total {
      display: flex;
      justify-content: space-between;
      padding: 1.5rem 0 0;
      margin-top: 1rem;
      border-top: 3px solid #e8f5e9;
      font-size: 1.3rem;
      font-weight: 800;
    }

    .order-total-label {
      color: #1a1a1a;
    }

    .order-total-amount {
      color: #2e7d32;
    }

    .security-badge {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      padding: 1rem;
      background: linear-gradient(135deg, #f0f9f4 0%, #e8f5e9 100%);
      border-radius: 12px;
      margin-top: 1rem;
      font-size: 0.9rem;
      color: #2e7d32;
      font-weight: 600;
    }

    .submit-btn {
      background: linear-gradient(135deg, #2e7d32 0%, #43a047 100%);
      color: white;
      border: none;
      padding: 1.3rem;
      border-radius: 16px;
      font-size: 1.15rem;
      font-weight: 800;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 8px 24px rgba(46, 125, 50, 0.4);
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-top: 1rem;
      position: relative;
      overflow: hidden;
    }

    .submit-btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
      transition: left 0.6s ease;
    }

    .submit-btn:hover::before {
      left: 100%;
    }

    .submit-btn:hover {
      background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%);
      transform: translateY(-3px);
      box-shadow: 0 12px 32px rgba(46, 125, 50, 0.5);
    }

    .submit-btn:active {
      transform: translateY(-1px);
    }

    .back-btn {
      background: transparent;
      border: 2px solid #e0e0e0;
      color: #666;
      padding: 1rem;
      border-radius: 12px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-top: 1rem;
    }

    .back-btn:hover {
      border-color: #43a047;
      color: #43a047;
      transform: translateY(-2px);
    }

    .info-text {
      font-size: 0.85rem;
      color: #666;
      margin-top: 0.5rem;
      display: flex;
      align-items: center;
      gap: 0.3rem;
    }

    .nequi-animation {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(106, 27, 154, 0.95);
      z-index: 1000;
      justify-content: center;
      align-items: center;
      animation: fadeIn 0.3s ease;
    }

    .nequi-animation.show {
      display: flex;
    }

    .nequi-content {
      background: white;
      padding: 3rem;
      border-radius: 24px;
      text-align: center;
      animation: scaleIn 0.5s ease;
      max-width: 450px;
    }

    .nequi-logo {
      font-size: 6rem;
      margin-bottom: 1rem;
      animation: pulse 2s infinite;
    }

    .nequi-content h2 {
      font-size: 1.8rem;
      color: #6a1b9a;
      margin-bottom: 1rem;
    }

    .nequi-content p {
      color: #666;
      margin-bottom: 1.5rem;
      line-height: 1.6;
    }

    .nequi-phone-display {
      background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%);
      padding: 1.5rem;
      border-radius: 16px;
      font-size: 1.5rem;
      font-weight: 800;
      color: #6a1b9a;
      margin-bottom: 1.5rem;
      letter-spacing: 2px;
    }

    .loading-dots {
      display: flex;
      justify-content: center;
      gap: 0.5rem;
      margin: 1.5rem 0;
    }

    .loading-dots span {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      background: #6a1b9a;
      animation: bounce-dots 1.4s infinite ease-in-out both;
    }

    .loading-dots span:nth-child(1) {
      animation-delay: -0.32s;
    }

    .loading-dots span:nth-child(2) {
      animation-delay: -0.16s;
    }

    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.1); }
    }

    @keyframes bounce-dots {
      0%, 80%, 100% { 
        transform: scale(0);
        opacity: 0.5;
      } 
      40% { 
        transform: scale(1);
        opacity: 1;
      }
    }

    .success-animation {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.9);
      z-index: 1000;
      justify-content: center;
      align-items: center;
      animation: fadeIn 0.3s ease;
    }

    .success-animation.show {
      display: flex;
    }

    .success-content {
      background: white;
      padding: 3rem;
      border-radius: 24px;
      text-align: center;
      animation: scaleIn 0.5s ease;
      max-width: 400px;
    }

    .success-icon {
      font-size: 5rem;
      margin-bottom: 1rem;
      animation: bounce 1s ease;
    }

    .success-content h2 {
      font-size: 2rem;
      color: #2e7d32;
      margin-bottom: 1rem;
    }

    .success-content p {
      color: #666;
      margin-bottom: 2rem;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    @keyframes scaleIn {
      from { transform: scale(0.8); opacity: 0; }
      to { transform: scale(1); opacity: 1; }
    }

    @keyframes bounce {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-20px); }
    }

    @media (max-width: 768px) {
      .payment-container {
        grid-template-columns: 1fr;
      }

      .payment-left,
      .payment-right {
        padding: 2rem;
      }

      .form-row {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <div class="payment-container">
    <div class="payment-left">
      <div class="logo-section">
        <span class="emoji">🌿</span>
        <h1>MI<span style="color: #ff9800;">HIERIBAL</span></h1>
      </div>

      <h2 class="section-title">💳 Información de Pago</h2>

      <div class="payment-methods">
        <button class="method-btn active" onclick="selectMethod('card')">
          <span class="emoji">💳</span>
          <span>Tarjeta</span>
        </button>
        <button class="method-btn" onclick="selectMethod('nequi')">
          <span class="emoji">💜</span>
          <span>Nequi</span>
        </button>
        <button class="method-btn" onclick="selectMethod('pse')">
          <span class="emoji">🏦</span>
          <span>PSE</span>
        </button>
        <button class="method-btn" onclick="selectMethod('efecty')">
          <span class="emoji">💵</span>
          <span>Efecty</span>
        </button>
      </div>

      <form id="paymentForm">
        <div id="cardFields">
          <div class="form-group">
            <label>Nombre del Titular</label>
            <input type="text" placeholder="Juan Pérez" required>
          </div>

          <div class="form-group card-input">
            <label>Número de Tarjeta</label>
            <input type="text" placeholder="1234 5678 9012 3456" maxlength="19" required>
            <span class="card-icon">💳</span>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Fecha de Expiración</label>
              <input type="text" placeholder="MM/AA" maxlength="5" required>
            </div>
            <div class="form-group">
              <label>CVV</label>
              <input type="text" placeholder="123" maxlength="3" required>
            </div>
          </div>
        </div>

        <div id="nequiFields" style="display: none;">
          <div style="background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%); padding: 1.5rem; border-radius: 16px; margin-bottom: 1.5rem;">
            <h4 style="color: #6a1b9a; font-size: 1.2rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
              💜 Paga con Nequi
            </h4>
            <p style="color: #4a148c; font-size: 0.95rem; line-height: 1.6; margin-bottom: 1rem;">
              Vamos a abrir tu app de Nequi para que envíes el pago directamente a nuestra cuenta.
            </p>
            <div style="background: white; padding: 1rem; border-radius: 12px; text-align: center;">
              <p style="color: #666; font-size: 0.9rem; margin-bottom: 0.5rem;">Monto a pagar:</p>
              <p style="font-size: 2rem; font-weight: 800; color: #6a1b9a;">$332.000</p>
            </div>
          </div>

          <div class="form-group">
            <label>Tu Número de Celular</label>
            <input type="tel" id="nequiPhone" placeholder="300 123 4567" maxlength="12" required>
            <div class="info-text">
              📱 Para enviarte la confirmación del pago
            </div>
          </div>

          <div class="form-group">
            <label>Tu Nombre Completo</label>
            <input type="text" id="nequiName" placeholder="Como aparece en Nequi" required>
          </div>

          <div style="background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%); padding: 1.5rem; border-radius: 16px; margin-top: 1rem;">
            <h4 style="color: #6a1b9a; font-size: 1.1rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
              💜 Cómo pagar con Nequi
            </h4>
            <ol style="color: #4a148c; font-size: 0.95rem; padding-left: 1.2rem; line-height: 1.8;">
              <li>Haz clic en <strong>"Pagar con Nequi"</strong></li>
              <li>Se abrirá tu <strong>app de Nequi automáticamente</strong></li>
              <li>Verifica el monto: <strong>$332.000</strong></li>
              <li>Envía el dinero al número: <strong>313 225 4044</strong></li>
              <li>Toma captura del comprobante y súbelo aquí</li>
            </ol>
          </div>

          <div class="info-text" style="margin-top: 1rem; background: #fff3e0; padding: 1rem; border-radius: 8px; color: #e65100;">
            ⚠️ Asegúrate de tener saldo suficiente en tu cuenta Nequi
          </div>
        </div>

        <div id="pseFields" style="display: none;">
          <div class="form-group">
            <label>Banco</label>
            <select required>
              <option value="">Selecciona tu banco</option>
              <option>Bancolombia</option>
              <option>Banco de Bogotá</option>
              <option>Davivienda</option>
              <option>BBVA</option>
              <option>Banco Popular</option>
            </select>
          </div>

          <div class="form-group">
            <label>Tipo de Persona</label>
            <select required>
              <option value="">Selecciona</option>
              <option>Natural</option>
              <option>Jurídica</option>
            </select>
          </div>

          <div class="form-group">
            <label>Número de Documento</label>
            <input type="text" placeholder="1234567890" required>
          </div>
        </div>

        <div id="efectyFields" style="display: none;">
          <div class="form-group">
            <label>Número de Documento</label>
            <input type="text" placeholder="1234567890" required>
          </div>

          <div class="form-group">
            <label>Email</label>
            <input type="email" placeholder="correo@ejemplo.com" required>
          </div>

          <div class="info-text">
            ℹ️ Recibirás un código para pagar en cualquier punto Efecty
          </div>
        </div>

        <h2 class="section-title" style="margin-top: 2rem;">📍 Información de Envío</h2>

        <div class="form-group">
          <label>Dirección</label>
          <input type="text" placeholder="Calle 123 #45-67" required>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Ciudad</label>
            <input type="text" placeholder="Bogotá" required>
          </div>
          <div class="form-group">
            <label>Teléfono</label>
            <input type="tel" placeholder="300 123 4567" required>
          </div>
        </div>

        <div class="security-badge">
          🔒 Pago 100% seguro y encriptado
        </div>

        <button type="submit" class="submit-btn">
          🛡️ Procesar Pago Seguro
        </button>

        <button type="button" class="back-btn" onclick="goBack()">
          ← Volver al Carrito
        </button>
      </form>
    </div>

    <div class="payment-right">
      <h2 class="section-title">📦 Resumen del Pedido</h2>

      <div class="order-summary">
        <div class="order-item">
          <span class="order-item-name">Proteína Whey Premium x2</span>
          <span class="order-item-price">$240.000</span>
        </div>
        <div class="order-item">
          <span class="order-item-name">Vitamina C Natural x1</span>
          <span class="order-item-price">$45.000</span>
        </div>
        <div class="order-item">
          <span class="order-item-name">Té Verde Orgánico x1</span>
          <span class="order-item-price">$32.000</span>
        </div>
        <div class="order-item">
          <span class="order-item-name">Subtotal</span>
          <span class="order-item-price">$317.000</span>
        </div>
        <div class="order-item">
          <span class="order-item-name">Envío</span>
          <span class="order-item-price">$15.000</span>
        </div>
        <div class="order-total">
          <span class="order-total-label">Total a Pagar</span>
          <span class="order-total-amount">$332.000</span>
        </div>
      </div>

      <div style="background: white; padding: 1.5rem; border-radius: 16px; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);">
        <h3 style="font-size: 1.1rem; margin-bottom: 1rem; color: #1a1a1a;">✨ Beneficios</h3>
        <div style="display: flex; flex-direction: column; gap: 0.8rem; font-size: 0.9rem; color: #666;">
          <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span>✅</span>
            <span>Envío gratuito en compras +$100.000</span>
          </div>
          <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span>✅</span>
            <span>Garantía de satisfacción 30 días</span>
          </div>
          <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span>✅</span>
            <span>Productos 100% naturales</span>
          </div>
          <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span>✅</span>
            <span>Devolución fácil</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="nequi-animation" id="nequiAnimation">
    <div class="nequi-content">
      <div class="nequi-logo">💜</div>
      <h2>Realiza tu pago en Nequi</h2>
      
      <div style="background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%); padding: 1.5rem; border-radius: 16px; margin: 1.5rem 0;">
        <p style="color: #4a148c; font-size: 0.9rem; margin-bottom: 1rem;">Envía el pago a:</p>
        <div class="nequi-phone-display">
          313 225 4044
        </div>
        <p style="color: #6a1b9a; font-weight: 700; font-size: 1.3rem;">Monto: $332.000</p>
      </div>

      <button id="openNequiBtn" class="submit-btn" style="margin-bottom: 1rem;">
        💜 Abrir App Nequi
      </button>

      <div style="text-align: left; margin: 1.5rem 0; padding: 1.5rem; background: #f5f5f5; border-radius: 12px;">
        <p style="font-weight: 700; margin-bottom: 1rem; color: #333;">📸 Sube tu comprobante de pago:</p>
        <input type="file" id="nequiReceipt" accept="image/*" style="width: 100%; padding: 0.8rem; border: 2px dashed #6a1b9a; border-radius: 8px; background: white; cursor: pointer;">
        <p style="font-size: 0.85rem; color: #666; margin-top: 0.5rem;">Acepta: JPG, PNG (Máx. 5MB)</p>
        
        <div id="receiptPreview" style="display: none; margin-top: 1rem;">
          <p style="font-weight: 600; color: #2e7d32; margin-bottom: 0.5rem;">✅ Comprobante recibido</p>
          <img id="receiptImage" style="max-width: 100%; border-radius: 8px; border: 2px solid #2e7d32;">
        </div>
      </div>

      <button id="confirmNequiBtn" class="submit-btn" style="display: none;">
        ✅ Confirmar Pago
      </button>

      <p style="font-size: 0.85rem; color: #666; margin-top: 1rem;">
        Una vez realices el pago y subas el comprobante, verificaremos tu pedido
      </p>

      <button class="back-btn" onclick="cancelNequi()" style="margin-top: 1rem;">
        ← Cancelar
      </button>
    </div>
  </div>

  <div class="success-animation" id="successAnimation">
    <div class="success-content">
      <div class="success-icon">✅</div>
      <h2>¡Pago Exitoso!</h2>
      <p>Tu pedido ha sido procesado correctamente. Recibirás un correo de confirmación en breve.</p>
      <button class="submit-btn" onclick="closeSuccess()">Entendido</button>
    </div>
  </div>

  <script>
    let currentMethod = 'card';
    const NEQUI_NUMBER = '3132254044';
    const TOTAL_AMOUNT = '332000';

    function selectMethod(method) {
      currentMethod = method;
      
      // Actualizar botones
      document.querySelectorAll('.method-btn').forEach(btn => {
        btn.classList.remove('active');
      });
      event.target.closest('.method-btn').classList.add('active');

      // Mostrar campos correspondientes
      document.getElementById('cardFields').style.display = method === 'card' ? 'block' : 'none';
      document.getElementById('nequiFields').style.display = method === 'nequi' ? 'block' : 'none';
      document.getElementById('pseFields').style.display = method === 'pse' ? 'block' : 'none';
      document.getElementById('efectyFields').style.display = method === 'efecty' ? 'block' : 'none';
    }

    document.getElementById('paymentForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const btn = document.querySelector('.submit-btn');
      
      if (currentMethod === 'nequi') {
        // Mostrar pantalla de pago Nequi
        document.getElementById('nequiAnimation').classList.add('show');
        
        // Configurar el botón de abrir Nequi
        document.getElementById('openNequiBtn').onclick = function() {
          openNequiApp();
        };
        
      } else {
        // Proceso normal para otros métodos
        btn.textContent = '⏳ Procesando...';
        btn.disabled = true;

        setTimeout(() => {
          document.getElementById('successAnimation').classList.add('show');
          btn.textContent = '🛡️ Procesar Pago Seguro';
          btn.disabled = false;
        }, 2000);
      }
    });

    function openNequiApp() {
      const phoneNumber = NEQUI_NUMBER;
      const amount = TOTAL_AMOUNT;
      const message = 'Pago MiHieribal - Pedido #' + Date.now().toString().slice(-6);
      
      // Intentar abrir la app de Nequi con deep link
      // Formato: nequi://sendMoney?phone=NUMBER&amount=AMOUNT&message=MESSAGE
      const nequiDeepLink = `nequi://sendMoney?phone=${phoneNumber}&amount=${amount}&message=${encodeURIComponent(message)}`;
      
      // Alternativa: URL que funciona en navegadores móviles
      const nequiWebLink = `https://nequi.com.co/send?phone=${phoneNumber}&amount=${amount}`;
      
      // Intentar abrir el deep link
      window.location.href = nequiDeepLink;
      
      // Fallback: abrir en nueva ventana después de 1 segundo si no funciona
      setTimeout(() => {
        // Mostrar instrucciones alternativas
        alert('Si no se abrió Nequi automáticamente:\n\n1. Abre tu app Nequi manualmente\n2. Envía $332.000 al número: 313 225 4044\n3. En el concepto escribe: ' + message + '\n4. Toma captura y súbela aquí');
      }, 1000);
    }

    // Manejar carga de comprobante
    document.getElementById('nequiReceipt')?.addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        // Validar tamaño (5MB máximo)
        if (file.size > 5 * 1024 * 1024) {
          alert('El archivo es muy grande. Máximo 5MB');
          return;
        }
        
        // Validar tipo
        if (!file.type.startsWith('image/')) {
          alert('Por favor sube una imagen');
          return;
        }
        
        // Mostrar preview
        const reader = new FileReader();
        reader.onload = function(event) {
          document.getElementById('receiptImage').src = event.target.result;
          document.getElementById('receiptPreview').style.display = 'block';
          document.getElementById('confirmNequiBtn').style.display = 'block';
        };
        reader.readAsDataURL(file);
      }
    });

    // Confirmar pago con comprobante
    document.getElementById('confirmNequiBtn')?.addEventListener('click', function() {
      const customerPhone = document.getElementById('nequiPhone').value;
      const customerName = document.getElementById('nequiName').value;
      const receiptFile = document.getElementById('nequiReceipt').files[0];
      
      if (!receiptFile) {
        alert('Por favor sube el comprobante de pago');
        return;
      }
      
      // Aquí deberías enviar los datos a tu backend
      // Por ahora simulamos el proceso
      this.textContent = '⏳ Verificando pago...';
      this.disabled = true;
      
      setTimeout(() => {
        // Cerrar modal de Nequi y mostrar éxito
        document.getElementById('nequiAnimation').classList.remove('show');
        document.getElementById('successAnimation').classList.add('show');
        
        // En producción, aquí enviarías:
        // - receiptFile (imagen del comprobante)
        // - customerPhone
        // - customerName
        // - TOTAL_AMOUNT
        // a tu servidor para verificación manual
        
        console.log('Datos para enviar al servidor:', {
          phone: customerPhone,
          name: customerName,
          amount: TOTAL_AMOUNT,
          nequiDestination: NEQUI_NUMBER,
          receipt: 'archivo de imagen'
        });
      }, 2000);
    });

    function cancelNequi() {
      if (confirm('¿Deseas cancelar el pago con Nequi?')) {
        document.getElementById('nequiAnimation').classList.remove('show');
        // Limpiar campos
        document.getElementById('nequiReceipt').value = '';
        document.getElementById('receiptPreview').style.display = 'none';
        document.getElementById('confirmNequiBtn').style.display = 'none';
      }
    }

    function closeSuccess() {
      document.getElementById('successAnimation').classList.remove('show');
    }

    function goBack() {
      if (confirm('¿Estás seguro de que deseas volver al carrito?')) {
        window.history.back();
      }
    }

    // Formatear número de Nequi
    document.getElementById('nequiPhone')?.addEventListener('input', function(e) {
      let value = e.target.value.replace(/\D/g, '');
      if (value.length > 3 && value.length <= 6) {
        value = value.substring(0, 3) + ' ' + value.substring(3);
      } else if (value.length > 6) {
        value = value.substring(0, 3) + ' ' + value.substring(3, 6) + ' ' + value.substring(6, 10);
      }
      e.target.value = value;
    });

    // Formatear número de tarjeta
    document.querySelector('input[placeholder="1234 5678 9012 3456"]')?.addEventListener('input', function(e) {
      let value = e.target.value.replace(/\s/g, '');
      let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
      e.target.value = formattedValue;
    });

    // Formatear fecha de expiración
    document.querySelector('input[placeholder="MM/AA"]')?.addEventListener('input', function(e) {
      let value = e.target.value.replace(/\D/g, '');
      if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
      }
      e.target.value = value;
    });
  </script>
</body>
</html>