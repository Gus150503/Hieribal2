<?php $base = $this->config['app']['base_url']; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Hieribal - Tienda Online</title>
    <link rel="stylesheet" href="<?= $base ?>/assets/css/carritohomepage.css">
</head>

<body>

    <!-- ========================================
         HEADER - NAVEGACIÓN PRINCIPAL
    ========================================= -->
    <header class="header">
        <div class="header-content">
            <div class="logo">
                🌿 MI<span>HIERIBAL</span>
            </div>
            <div class="profile-mini">
                <img src="<?= $base ?>/assets/img/Avatar.jfif" alt="Usuario">
                <div>
                    <div style="font-weight: 600;">Jose Perez</div>
                    <div style="font-size: 13px; opacity: 0.9;">jose@gmail.com</div>
                </div>
            </div>
        </div>
    </header>

    <!-- ========================================
         BUSCADOR Y FILTROS DE CATEGORÍAS
    ========================================= -->
    <section class="search-section">
        <div class="search-container">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Buscar productos...">
                <button type="button">🔍</button>
            </div>

            <div class="categories">
                <button class="cat-btn active" data-category="all">✨ Todos</button>
                <button class="cat-btn" data-category="proteinas">🌱 Proteínas</button>
                <button class="cat-btn" data-category="tes">🍵 Tés y Aromáticas</button>
                <button class="cat-btn" data-category="vitaminas">💊 Vitaminas</button>
                <button class="cat-btn" data-category="organicos">🥗 Orgánicos</button>
                <button class="cat-btn" data-category="mieles">🍯 Mieles</button>
                <button class="cat-btn" data-category="cereales">🌾 Cereales</button>
            </div>
        </div>
    </section>

    <!-- ========================================
         CONTENEDOR PRINCIPAL (PRODUCTOS + CARRITO)
    ========================================= -->
    <div class="main-container">

        <!-- COLUMNA DE PRODUCTOS -->
        <div class="products-container">

            <!-- ========================================
                 CATEGORÍA: PROTEÍNAS
            ========================================= -->
            <section class="category-section" id="proteinas">
                <div class="category-header">
                    <span class="emoji">🌱</span>
                    <h2>Proteínas</h2>
                </div>
                <div class="products-grid">

                    <div class="product-card">
                        <img src="<?= $base ?>/assets/img/gym1.png" alt="Proteína Whey Amarilla" class="product-image">
                        <h3>Proteína Whey Amarilla</h3>
                        <div class="product-price">$80.000</div>
                        <button class="add-to-cart-btn"
                            onclick="addToCart(1, 'Proteína Whey Amarilla', 80000, '<?= $base ?>/assets/img/gym1.png')">
                            🛒 Añadir al carrito
                        </button>
                    </div>

                    <div class="product-card">
                        <img src="<?= $base ?>/assets/img/gym2.jfif" alt="Proteína Whey Premium" class="product-image">
                        <h3>Proteína Whey Premium</h3>
                        <div class="product-price">$120.000</div>
                        <button class="add-to-cart-btn"
                            onclick="addToCart(2, 'Proteína Whey Premium', 120000, '<?= $base ?>/assets/img/gym2.jfif')">
                            🛒 Añadir al carrito
                        </button>
                    </div>

                    <div class="product-card">
                        <img src="<?= $base ?>/assets/img/gym3.jfif" alt="Proteína Clásica" class="product-image">
                        <h3>Proteína Clásica</h3>
                        <div class="product-price">$90.000</div>
                        <button class="add-to-cart-btn"
                            onclick="addToCart(3, 'Proteína Clásica', 90000, '<?= $base ?>/assets/img/gym3.jfif')">
                            🛒 Añadir al carrito
                        </button>
                    </div>

                    <div class="product-card">
                        <img src="<?= $base ?>/assets/img/gym4.jfif" alt="Combo de Proteínas" class="product-image">
                        <h3>Combo de Proteínas</h3>
                        <div class="product-price">$200.000</div>
                        <button class="add-to-cart-btn"
                            onclick="addToCart(4, 'Combo de Proteínas', 200000, '<?= $base ?>/assets/img/gym4.jfif')">
                            🛒 Añadir al carrito
                        </button>
                    </div>

                    <div class="product-card">
                        <img src="<?= $base ?>/assets/img/gym5.jfif" alt="Proteína de Fresa" class="product-image">
                        <h3>Proteína de Fresa</h3>
                        <div class="product-price">$95.000</div>
                        <button class="add-to-cart-btn"
                            onclick="addToCart(5, 'Proteína de Fresa', 95000, '<?= $base ?>/assets/img/gym5.jfif')">
                            🛒 Añadir al carrito
                        </button>
                    </div>

                    <div class="product-card">
                        <img src="<?= $base ?>/assets/img/gym6.jfif" alt="Proteína Whey Iso" class="product-image">
                        <h3>Proteína Whey Iso</h3>
                        <div class="product-price">$110.000</div>
                        <button class="add-to-cart-btn"
                            onclick="addToCart(6, 'Proteína Whey Iso', 110000, '<?= $base ?>/assets/img/gym6.jfif')">
                            🛒 Añadir al carrito
                        </button>
                    </div>

                    <div class="product-card">
                        <img src="<?= $base ?>/assets/img/gym7.jfif" alt="Proteína Sabor Vainilla"
                            class="product-image">
                        <h3>Proteína Sabor Vainilla</h3>
                        <div class="product-price">$40.000</div>
                        <button class="add-to-cart-btn"
                            onclick="addToCart(7, 'Proteína Sabor Vainilla', 40000, '<?= $base ?>/assets/img/gym7.jfif')">
                            🛒 Añadir al carrito
                        </button>
                    </div>

                    <div class="product-card">
                        <img src="<?= $base ?>/assets/img/gym8.jfif" alt="Proteína Total" class="product-image">
                        <h3>Proteína Total</h3>
                        <div class="product-price">$80.000</div>
                        <button class="add-to-cart-btn"
                            onclick="addToCart(8, 'Proteína Total', 80000, '<?= $base ?>/assets/img/gym8.jfif')">
                            🛒 Añadir al carrito
                        </button>
                    </div>

                </div>
            </section>

            <!-- ========================================
                 CATEGORÍA: TÉS Y AROMÁTICAS
            ========================================= -->
            <section class="category-section" id="tes">
                <div class="category-header">
                    <span class="emoji">🍵</span>
                    <h2>Tés y Aromáticas</h2>
                </div>
                <div class="products-grid">

                    <div class="product-card">
                        <img src="https://images.unsplash.com/photo-1594631252845-29fc4cc8cde9?w=400&h=300&fit=crop"
                            alt="Té Verde" class="product-image">
                        <h3>Té Verde Orgánico</h3>
                        <div class="product-price">$25.000</div>
                        <button class="add-to-cart-btn"
                            onclick="addToCart(101, 'Té Verde Orgánico', 25000, 'https://images.unsplash.com/photo-1594631252845-29fc4cc8cde9?w=400&h=300&fit=crop')">
                            🛒 Añadir al carrito
                        </button>
                    </div>

                    <div class="product-card">
                        <img src="https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=400&h=300&fit=crop"
                            alt="Té Negro" class="product-image">
                        <h3>Té Negro Premium</h3>
                        <div class="product-price">$28.000</div>
                        <button class="add-to-cart-btn"
                            onclick="addToCart(102, 'Té Negro Premium', 28000, 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=400&h=300&fit=crop')">
                            🛒 Añadir al carrito
                        </button>
                    </div>

                    <div class="product-card">
                        <img src="https://images.unsplash.com/photo-1587593810167-a84920ea0781?w=400&h=300&fit=crop"
                            alt="Manzanilla" class="product-image">
                        <h3>Manzanilla Natural</h3>
                        <div class="product-price">$18.000</div>
                        <button class="add-to-cart-btn"
                            onclick="addToCart(103, 'Manzanilla Natural', 18000, 'https://images.unsplash.com/photo-1587593810167-a84920ea0781?w=400&h=300&fit=crop')">
                            🛒 Añadir al carrito
                        </button>
                    </div>

                    <div class="product-card">
                        <img src="https://images.unsplash.com/photo-1563822249548-9a72b6353cd1?w=400&h=300&fit=crop"
                            alt="Té Rojo" class="product-image">
                        <h3>Té Rojo Pu-erh</h3>
                        <div class="product-price">$32.000</div>
                        <button class="add-to-cart-btn"
                            onclick="addToCart(104, 'Té Rojo Pu-erh', 32000, 'https://images.unsplash.com/photo-1563822249548-9a72b6353cd1?w=400&h=300&fit=crop')">
                            🛒 Añadir al carrito
                        </button>
                    </div>

                </div>
            </section>

            <!-- ========================================
                 CATEGORÍA: VITAMINAS
            ========================================= -->
            <section class="category-section" id="vitaminas">
                <div class="category-header">
                    <span class="emoji">💊</span>
                    <h2>Vitaminas</h2>
                </div>
                <div class="products-grid">

                    <div class="product-card">
                        <img src="https://images.unsplash.com/photo-1550572017-4fade1c42d9c?w=400&h=300&fit=crop"
                            alt="Vitamina C" class="product-image">
                        <h3>Vitamina C 1000mg</h3>
                        <div class="product-price">$35.000</div>
                        <button class="add-to-cart-btn"
                            onclick="addToCart(201, 'Vitamina C 1000mg', 35000, 'https://images.unsplash.com/photo-1550572017-4fade1c42d9c?w=400&h=300&fit=crop')">
                            🛒 Añadir al carrito
                        </button>
                    </div>

                    <div class="product-card">
                        <img src="https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=400&h=300&fit=crop"
                            alt="Complejo B" class="product-image">
                        <h3>Complejo B12</h3>
                        <div class="product-price">$42.000</div>
                        <button class="add-to-cart-btn"
                            onclick="addToCart(202, 'Complejo B12', 42000, 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=400&h=300&fit=crop')">
                            🛒 Añadir al carrito
                        </button>
                    </div>

                    <div class="product-card">
                        <img src="https://images.unsplash.com/photo-1526256262350-7da7584cf5eb?w=400&h=300&fit=crop"
                            alt="Vitamina D3" class="product-image">
                        <h3>Vitamina D3 5000 IU</h3>
                        <div class="product-price">$38.000</div>
                        <button class="add-to-cart-btn"
                            onclick="addToCart(203, 'Vitamina D3 5000 IU', 38000, 'https://images.unsplash.com/photo-1526256262350-7da7584cf5eb?w=400&h=300&fit=crop')">
                            🛒 Añadir al carrito
                        </button>
                    </div>

                    <div class="product-card">
                        <img src="https://images.unsplash.com/photo-1607619056574-7b8d3ee536b2?w=400&h=300&fit=crop"
                            alt="Multivitamínico" class="product-image">
                        <h3>Multivitamínico Completo</h3>
                        <div class="product-price">$55.000</div>
                        <button class="add-to-cart-btn"
                            onclick="addToCart(204, 'Multivitamínico Completo', 55000, 'https://images.unsplash.com/photo-1607619056574-7b8d3ee536b2?w=400&h=300&fit=crop')">
                            🛒 Añadir al carrito
                        </button>
                    </div>

                    <div class="product-card">
                        <img src="https://images.unsplash.com/photo-1505751172876-fa1923c5c528?w=400&h=300&fit=crop"
                            alt="Omega 3" class="product-image">
                        <h3>Omega 3 Fish Oil</h3>
                        <div class="product-price">$48.000</div>
                        <button class="add-to-cart-btn"
                            onclick="addToCart(205, 'Omega 3 Fish Oil', 48000, 'https://images.unsplash.com/photo-1505751172876-fa1923c5c528?w=400&h=300&fit=crop')">
                            🛒 Añadir al carrito
                        </button>
                    </div>

                </div>
            </section>

            <!-- ========================================
                 CATEGORÍA: ORGÁNICOS
            ========================================= -->
            <section class="category-section" id="organicos">
                <div class="category-header">
                    <span class="emoji">🥗</span>
                    <h2>Orgánicos</h2>
                </div>
                <div class="products-grid">

                    <div class="product-card">
                        <img src="https://images.unsplash.com/photo-1586201375761-83865001e31c?w=400&h=300&fit=crop"
                            alt="Quinoa" class="product-image">
                        <h3>Quinoa Orgánica 500g</h3>
                        <div class="product-price">$22.000</div>
                        <button class="add-to-cart-btn"
                            onclick="addToCart(301, 'Quinoa Orgánica 500g', 22000, 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=400&h=300&fit=crop')">
                            🛒 Añadir al carrito
                        </button>
                    </div>

                    <div class="product-card">
                        <img src="https://images.unsplash.com/photo-1612195583950-b8fd34c87093?w=400&h=300&fit=crop"
                            alt="Semillas de Chía" class="product-image">
                        <h3>Semillas de Chía Orgánicas</h3>
                        <div class="product-price">$18.000</div>
                        <button class="add-to-cart-btn"
                            onclick="addToCart(302, 'Semillas de Chía Orgánicas', 18000, 'https://images.unsplash.com/photo-1612195583950-b8fd34c87093?w=400&h=300&fit=crop')">
                            🛒 Añadir al carrito
                        </button>
                    </div>

                    <div class="product-card">
                        <img src="https://images.unsplash.com/photo-1610648425568-f4c86e556737?w=400&h=300&fit=crop"
                            alt="Linaza" class="product-image">
                        <h3>Linaza Dorada Orgánica</h3>
                        <div class="product-price">$15.000</div>
                        <button class="add-to-cart-btn"
                            onclick="addToCart(303, 'Linaza Dorada Orgánica', 15000, 'https://images.unsplash.com/photo-1610648425568-f4c86e556737?w=400&h=300&fit=crop')">
                            🛒 Añadir al carrito
                        </button>
                    </div>

                    <div class="product-card">
                        <img src="https://images.unsplash.com/photo-1619566636858-adf3ef46400b?w=400&h=300&fit=crop"
                            alt="Spirulina" class="product-image">
                        <h3>Spirulina en Polvo 250g</h3>
                        <div class="product-price">$45.000</div>
                        <button class="add-to-cart-btn"
                            onclick="addToCart(304, 'Spirulina en Polvo 250g', 45000, 'https://images.unsplash.com/photo-1619566636858-adf3ef46400b?w=400&h=300&fit=crop')">
                            🛒 Añadir al carrito
                        </button>
                    </div>

                </div>
            </section>

            <!-- ========================================
                 CATEGORÍA: MIELES
            ========================================= -->
            <section class="category-section" id="mieles">
                <div class="category-header">
                    <span class="emoji">🍯</span>
                    <h2>Mieles</h2>
                </div>
                <div class="products-grid">

                    <div class="product-card">
                        <img src="https://images.unsplash.com/photo-1587049352846-4a222e784e38?w=400&h=300&fit=crop"
                            alt="Miel Pura" class="product-image">
                        <h3>Miel de Abeja Pura 500g</h3>
                        <div class="product-price">$28.000</div>
                        <button class="add-to-cart-btn"
                            onclick="addToCart(401, 'Miel de Abeja Pura 500g', 28000, 'https://images.unsplash.com/photo-1587049352846-4a222e784e38?w=400&h=300&fit=crop')">
                            🛒 Añadir al carrito
                        </button>
                    </div>

                    <div class="product-card">
                        <img src="https://images.unsplash.com/photo-1558642452-9d2a7deb7f62?w=400&h=300&fit=crop"
                            alt="Miel Orgánica" class="product-image">
                        <h3>Miel Orgánica Premium</h3>
                        <div class="product-price">$35.000</div>
                        <button class="add-to-cart-btn"
                            onclick="addToCart(402, 'Miel Orgánica Premium', 35000, 'https://images.unsplash.com/photo-1558642452-9d2a7deb7f62?w=400&h=300&fit=crop')">
                            🛒 Añadir al carrito
                        </button>
                    </div>

                    <div class="product-card">
                        <img src="https://images.unsplash.com/photo-1587735243615-c03f25aaff15?w=400&h=300&fit=crop"
                            alt="Miel Manuka" class="product-image">
                        <h3>Miel de Manuka UMF 15+</h3>
                        <div class="product-price">$85.000</div>
                        <button class="add-to-cart-btn"
                            onclick="addToCart(403, 'Miel de Manuka UMF 15+', 85000, 'https://images.unsplash.com/photo-1587735243615-c03f25aaff15?w=400&h=300&fit=crop')">
                            🛒 Añadir al carrito
                        </button>
                    </div>

                    <div class="product-card">
                        <img src="https://images.unsplash.com/photo-1608797178974-15b35a64ede9?w=400&h=300&fit=crop"
                            alt="Propóleo" class="product-image">
                        <h3>Propóleo Natural 30ml</h3>
                        <div class="product-price">$32.000</div>
                        <button class="add-to-cart-btn"
                            onclick="addToCart(404, 'Propóleo Natural 30ml', 32000, 'https://images.unsplash.com/photo-1608797178974-15b35a64ede9?w=400&h=300&fit=crop')">
                            🛒 Añadir al carrito
                        </button>
                    </div>

                </div>
            </section>

            <!-- ========================================
                 CATEGORÍA: CEREALES
            ========================================= -->
            <section class="category-section" id="cereales">
                <div class="category-header">
                    <span class="emoji">🌾</span>
                    <h2>Cereales</h2>
                </div>
                <div class="products-grid">

                    <div class="product-card">
                        <img src="https://images.unsplash.com/photo-1574163031327-3c46c4e23d42?w=400&h=300&fit=crop"
                            alt="Avena" class="product-image">
                        <h3>Avena en Hojuelas 1kg</h3>
                        <div class="product-price">$12.000</div>
                        <button class="add-to-cart-btn"
                            onclick="addToCart(501, 'Avena en Hojuelas 1kg', 12000, 'https://images.unsplash.com/photo-1574163031327-3c46c4e23d42?w=400&h=300&fit=crop')">
                            🛒 Añadir al carrito
                        </button>
                    </div>

                    <div class="product-card">
                        <img src="https://images.unsplash.com/photo-1526318896980-cf78c088247c?w=400&h=300&fit=crop"
                            alt="Granola" class="product-image">
                        <h3>Granola Artesanal 500g</h3>
                        <div class="product-price">$20.000</div>
                        <button class="add-to-cart-btn"
                            onclick="addToCart(502, 'Granola Artesanal 500g', 20000, 'https://images.unsplash.com/photo-1526318896980-cf78c088247c?w=400&h=300&fit=crop')">
                            🛒 Añadir al carrito
                        </button>
                    </div>

                    <div class="product-card">
                        <img src="https://images.unsplash.com/photo-1586444248902-2f64eddc13df?w=400&h=300&fit=crop"
                            alt="Amaranto" class="product-image">
                        <h3>Amaranto Inflado 300g</h3>
                        <div class="product-price">$16.000</div>
                        <button class="add-to-cart-btn"
                            onclick="addToCart(503, 'Amaranto Inflado 300g', 16000, 'https://images.unsplash.com/photo-1586444248902-2f64eddc13df?w=400&h=300&fit=crop')">
                            🛒 Añadir al carrito
                        </button>
                    </div>

                    <div class="product-card">
                        <img src="https://images.unsplash.com/photo-1601024445121-e5b82f020549?w=400&h=300&fit=crop"
                            alt="Mix Cereales" class="product-image">
                        <h3>Mix de Cereales Integrales</h3>
                        <div class="product-price">$25.000</div>
                        <button class="add-to-cart-btn"
                            onclick="addToCart(504, 'Mix de Cereales Integrales', 25000, 'https://images.unsplash.com/photo-1601024445121-e5b82f020549?w=400&h=300&fit=crop')">
                            🛒 Añadir al carrito
                        </button>
                    </div>

                </div>
            </section>

        </div>

        <!-- ========================================
             CARRITO DE COMPRAS (SIDEBAR)
        ========================================= -->
        <aside class="cart-sidebar">
            <div class="cart-header">
                <h2>🛒 Carrito</h2>
                <span class="cart-count" id="cartCount">0</span>
            </div>

            <div class="cart-items" id="cartItems">
                <div class="cart-empty">
                    <p>Tu carrito está vacío</p>
                    <p style="font-size: 48px; margin-top: 20px;">🛍️</p>
                </div>
            </div>

            <div class="cart-total">
                <div class="cart-total-label">Total:</div>
                <div class="cart-total-amount" id="cartTotal">$0</div>
            </div>

            <button class="checkout-btn" id="checkoutBtn" onclick="checkout()" disabled>
                Finalizar Compra
            </button>
        </aside>

    </div>

    <!-- ========================================
         JAVASCRIPT
    ========================================= -->
    <script src="<?= $base ?>/assets/js/carrito.js"></script>

</body>

</html>