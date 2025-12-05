

CREATE TABLE `administrador` (
  `IdAdministrador` int(11) NOT NULL,
  `UsuarioAdministrador` varchar(100) NOT NULL,
  `ContrasenaAdministrador` varchar(100) NOT NULL,
  `EmailAdministrador` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `administrador`
--

INSERT INTO `administrador` (`IdAdministrador`, `UsuarioAdministrador`, `ContrasenaAdministrador`, `EmailAdministrador`) VALUES
(1, 'admin01', '$2y$10$QGimDncpG6dYZUZaOdyNhu4YVtvjSlxyy6Tqmkq7ZRb7o/IHEF0JG', 'admin@barberia.com');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `IdCategorias` int(11) NOT NULL,
  `NombreCategorias` varchar(50) NOT NULL,
  `ActivoCategorias` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`IdCategorias`, `NombreCategorias`, `ActivoCategorias`) VALUES
(1, 'Vapes', 1),
(2, 'STLTH PODS', 1),
(3, 'POLO', 1),
(4, 'PIERCINGS', 1),
(5, 'ZAPATILLAS', 1),
(8, 'PRODUCTOS DE BARBERÍA', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalleventas`
--

CREATE TABLE `detalleventas` (
  `IdDetalleVentas` int(11) NOT NULL,
  `IdVentas` int(11) NOT NULL,
  `IdProductos` int(11) NOT NULL,
  `NombreDetalleVentas` varchar(200) NOT NULL,
  `CantidadDetalleVentas` int(11) NOT NULL,
  `PrecioDetalleVentas` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalleventas`
--

INSERT INTO `detalleventas` (`IdDetalleVentas`, `IdVentas`, `IdProductos`, `NombreDetalleVentas`, `CantidadDetalleVentas`, `PrecioDetalleVentas`) VALUES
(76, 45, 50, 'Cera Fijación Extrema', 1, 45.00),
(77, 45, 52, 'Bálsamo After Shave', 1, 35.00),
(78, 46, 51, 'Aceite para Barba de Sándalo', 1, 65.00),
(79, 46, 54, 'Peine de Madera para Barba', 1, 25.00),
(80, 46, 57, 'Gel de Afeitar Transparente', 1, 15.00),
(81, 47, 50, 'Cera Fijación Extrema', 2, 90.00),
(82, 47, 51, 'Aceite para Barba de Sándalo', 1, 65.00),
(83, 48, 53, 'Shampoo para Barba', 2, 80.00),
(84, 48, 56, 'Navaja Clásica con Hoja Intercambiable', 1, 30.00),
(85, 49, 59, 'Tónico Capilar Anti-Caída', 1, 75.00),
(86, 49, 58, 'Pomada Brillo Medio', 2, 100.00),
(87, 50, 54, 'Peine de Madera para Barba', 2, 25.00),
(88, 50, 59, 'Tónico Capilar Anti-Caída', 1, 75.00),
(89, 50, 50, 'Cera Fijación Extrema', 2, 45.00),
(90, 50, 51, 'Aceite para Barba de Sándalo', 2, 65.00),
(91, 50, 52, 'Bálsamo After Shave', 2, 35.00),
(92, 50, 55, 'Tijeras de Corte Profesional 5.5\"', 1, 150.00),
(93, 50, 58, 'Pomada Brillo Medio', 1, 50.00),
(94, 51, 51, 'Aceite para Barba de Sándalo', 1, 65.00),
(95, 51, 52, 'Bálsamo After Shave', 1, 35.00),
(96, 52, 55, 'Tijeras de Corte Profesional 5.5\"', 2, 150.00),
(97, 52, 51, 'Aceite para Barba de Sándalo', 1, 65.00),
(98, 53, 52, 'Bálsamo After Shave', 1, 35.00),
(99, 53, 50, 'Cera Fijación Extrema', 1, 45.00),
(100, 53, 56, 'Navaja Clásica con Hoja Intercambiable', 1, 85.00),
(101, 54, 51, 'Aceite para Barba de Sándalo', 3, 65.00),
(102, 54, 54, 'Peine de Madera para Barba', 3, 25.00),
(103, 54, 58, 'Pomada Brillo Medio', 3, 50.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

CREATE TABLE `empleados` (
  `IdEmpleados` int(11) NOT NULL,
  `NombreEmpleado` varchar(100) NOT NULL,
  `ApellidoEmpleados` varchar(100) NOT NULL,
  `DNIEmpleados` varchar(8) NOT NULL,
  `TelefonoEmpleados` varchar(9) NOT NULL,
  `EmailEmpleados` varchar(100) NOT NULL,
  `RolEmpleados` varchar(50) NOT NULL,
  `ContrasenaEmpleados` varchar(255) NOT NULL,
  `Activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empleados`
--

INSERT INTO `empleados` (`IdEmpleados`, `NombreEmpleado`, `ApellidoEmpleados`, `DNIEmpleados`, `TelefonoEmpleados`, `EmailEmpleados`, `RolEmpleados`, `ContrasenaEmpleados`, `Activo`) VALUES
(1, 'Daniel', 'Escudero', '87654321', '987654321', 'daniel@gmail.com', 'barbero', '$2y$10$Q/zIXndxUdotcgtLqSVcAuiuej4f9jTV4zKmpin63mnOcHTikjcPi', 1),
(2, 'Jean', 'Castañeda', '76543211', '987654332', 'j@gmail.com', 'barbero', '$2y$10$Q/zIXndxUdotcgtLqSVcAuiuej4f9jTV4zKmpin63mnOcHTikjcPi', 1),
(3, 'Adriana', 'Rodríguez', '73651708', '997216321', 'adriana@gmail.com', 'barbero', '$2y$10$Q/zIXndxUdotcgtLqSVcAuiuej4f9jTV4zKmpin63mnOcHTikjcPi', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imagenes`
--

CREATE TABLE `imagenes` (
  `IdImagen` int(11) NOT NULL,
  `Tipo` enum('servicio','producto','barbero') NOT NULL,
  `IdRelacionado` int(11) NOT NULL,
  `RutaImagen` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `imagenes`
--

INSERT INTO `imagenes` (`IdImagen`, `Tipo`, `IdRelacionado`, `RutaImagen`) VALUES
(1, 'servicio', 1, '../assets/img/servicios/corte_clasico.jpg'),
(2, 'servicio', 2, '../assets/img/servicios/corte_fade.jpg'),
(3, 'servicio', 3, '../assets/img/servicios/corte_premium.jpg'),
(4, 'servicio', 4, '../assets/img/servicios/corte_infantil.jpg'),
(5, 'servicio', 5, '../assets/img/servicios/corte_con_diseo_5.webp'),
(6, 'servicio', 6, '../assets/img/servicios/afeitado_clasico.jpg'),
(7, 'servicio', 7, '../assets/img/servicios/perfilado_de_barba_7.webp'),
(8, 'servicio', 8, '../assets/img/servicios/tinte_barba_8.webp'),
(9, 'servicio', 9, '../assets/img/servicios/masaje_capilar.jpg'),
(10, 'servicio', 10, '../assets/img/servicios/exfoliacion_facial.jpg'),
(11, 'servicio', 11, '../assets/img/servicios/tinte_capilar.jpg'),
(12, 'servicio', 12, '../assets/img/servicios/limpieza_facial_completa.jpg'),
(13, 'barbero', 1, '../assets/img/barberos/barbero1.jpg'),
(14, 'barbero', 2, '../assets/img/barberos/barbero2.jpg'),
(15, 'barbero', 3, '../assets/img/barberos/barbero3.jpg'),
(16, 'producto', 50, '../assets/img/productos/cera_fijacion_50.webp'),
(17, 'producto', 51, '../assets/img/productos/aceite_51.webp'),
(18, 'producto', 52, '../assets/img/productos/balsamo_52.webp'),
(19, 'producto', 61, '../assets/img/productos/producto_61_1760669916.webp'),
(20, 'producto', 53, '../assets/img/productos/shampoo_para_barba_53.webp'),
(21, 'producto', 54, '../assets/img/productos/peina_madero_54.webp'),
(22, 'producto', 55, '../assets/img/productos/tijeas_corte_profesional_55.webp'),
(23, 'producto', 56, '../assets/img/productos/navaja_56.webp'),
(24, 'producto', 57, '../assets/img/productos/gel_de_afeitar_transparente_57.webp'),
(25, 'producto', 58, '../assets/img/productos/cera_pomada_58.webp'),
(26, 'producto', 59, '../assets/img/productos/tonico_capilar_59.webp');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `locales`
--

CREATE TABLE `locales` (
  `IdLocales` int(11) NOT NULL,
  `NombreLocal` varchar(100) DEFAULT NULL,
  `DireccionLocal` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `locales`
--

INSERT INTO `locales` (`IdLocales`, `NombreLocal`, `DireccionLocal`) VALUES
(1, 'Ventanilla', 'Urb. Satélite, Ventanilla'),
(2, 'Surquillo', 'Av. Principal 456, Surquillo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `IdProductos` int(11) NOT NULL,
  `NombreProductos` varchar(200) NOT NULL,
  `DescripcionProductos` text NOT NULL,
  `PrecioProductos` decimal(10,2) NOT NULL,
  `StockProductos` int(11) NOT NULL DEFAULT 0,
  `IdCategorias` int(11) NOT NULL,
  `ActivoProductos` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`IdProductos`, `NombreProductos`, `DescripcionProductos`, `PrecioProductos`, `StockProductos`, `IdCategorias`, `ActivoProductos`) VALUES
(50, 'Cera Fijación Extrema', 'Cera a base de agua para un acabado mate y fijación ultra fuerte, ideal para peinados modernos.', 45.00, 12, 8, 1),
(51, 'Aceite para Barba de Sándalo', 'Mezcla de aceites naturales para hidratar y suavizar la barba y la piel debajo. Aroma a sándalo.', 65.00, 13, 8, 1),
(52, 'Bálsamo After Shave', 'Bálsamo post-afeitado que calma la piel y reduce la irritación. Contiene aloe vera.', 35.00, 14, 8, 1),
(53, 'Shampoo para Barba', 'Fórmula suave para limpiar profundamente la barba sin resecar. Uso diario.', 40.00, 25, 8, 1),
(54, 'Peine de Madera para Barba', 'Peine antiestático de madera de sándalo, perfecto para desenredar y distribuir el aceite.', 25.00, 25, 8, 1),
(55, 'Tijeras de Corte Profesional 5.5\"', 'Acero japonés, ergonómicas. Ideales para corte de precisión.', 150.00, 5, 8, 1),
(56, 'Navaja Clásica con Hoja Intercambiable', 'Acero inoxidable. Diseño ergonómico para afeitado clásico.', 85.00, 11, 8, 1),
(57, 'Gel de Afeitar Transparente', 'Permite una visión clara para perfilar barbas y líneas. No espumoso.', 30.00, 22, 8, 1),
(58, 'Pomada Brillo Medio', 'Pomada a base de aceite para un acabado brillante y fijación media. Aroma cítrico.', 50.00, 13, 8, 1),
(59, 'Tónico Capilar Anti-Caída', 'Loción para el cuero cabelludo que estimula el folículo y reduce la caída del cabello.', 75.00, 9, 8, 1),
(60, 'prueba', 'prueba1', 1.00, 1, 8, 0),
(61, 'prueba', 'prueba', 2.00, 3, 8, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `IdReservas` int(11) NOT NULL,
  `IdUsuarios` int(11) DEFAULT NULL,
  `IdServicios` int(11) DEFAULT NULL,
  `IdLocal` int(11) DEFAULT NULL,
  `IdBarberos` int(11) DEFAULT NULL,
  `IdTransaccion` varchar(50) DEFAULT NULL,
  `FechaReservas` date NOT NULL,
  `HoraReservas` time NOT NULL,
  `EstadoReservas` enum('Confirmado','No Confirmado','Completado','Cancelado') NOT NULL DEFAULT 'No Confirmado',
  `MotivoCancelacion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reservas`
--

INSERT INTO `reservas` (`IdReservas`, `IdUsuarios`, `IdServicios`, `IdLocal`, `IdBarberos`, `IdTransaccion`, `FechaReservas`, `HoraReservas`, `EstadoReservas`, `MotivoCancelacion`) VALUES
(2, 10, 6, NULL, 1, NULL, '2025-10-17', '11:30:00', 'Confirmado', NULL),
(3, 10, 2, NULL, 3, NULL, '2025-10-18', '10:30:00', 'Completado', NULL),
(4, 10, 6, NULL, 3, NULL, '2025-10-18', '10:00:00', 'Cancelado', NULL),
(6, 10, 2, NULL, 3, 'pi_3SUK1lL0CpOiDccy0acEomlc', '2025-11-22', '18:10:00', 'Cancelado', NULL),
(7, 10, 5, NULL, 3, 'pi_3SUK2pL0CpOiDccy0fPdd56F', '2025-11-17', '20:00:00', 'Cancelado', NULL),
(8, 10, 2, NULL, 2, 'pi_3SUK4ML0CpOiDccy1sWoL6Sk', '2025-11-14', '13:00:00', 'Confirmado', NULL),
(9, 10, 1, NULL, 2, 'pi_3SUfgYL0CpOiDccy04Ur4bUT', '2025-11-17', '11:30:00', 'Cancelado', NULL),
(10, 10, 5, NULL, 2, 'pi_3SUg6jL0CpOiDccy1ixzTm70', '2025-11-19', '11:00:00', 'Cancelado', 'No quise ir'),
(11, 10, 7, NULL, 1, 'pi_3SUgNiL0CpOiDccy0QRZsbjH', '2025-11-20', '11:30:00', 'Cancelado', 'asdssdfs'),
(13, 10, 1, NULL, 2, 'pi_3SUi6RL0CpOiDccy1qwdiJag', '2025-11-20', '10:45:00', 'Confirmado', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios`
--

CREATE TABLE `servicios` (
  `IdServicios` int(11) NOT NULL,
  `TipoServicios` varchar(100) DEFAULT NULL,
  `PrecioServicios` decimal(18,2) NOT NULL,
  `DescripcionServicios` text DEFAULT NULL,
  `DuracionMinutos` int(10) NOT NULL,
  `ActivoServicios` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `servicios`
--

INSERT INTO `servicios` (`IdServicios`, `TipoServicios`, `PrecioServicios`, `DescripcionServicios`, `DuracionMinutos`, `ActivoServicios`) VALUES
(1, 'Corte Clásico', 25.00, 'Corte tradicional con peine y tijera, estilo limpio y profesional.', 30, 1),
(2, 'Corte Fade', 35.00, 'Corte degradado moderno con máquina y navaja.', 40, 1),
(3, 'Corte Premium', 55.00, 'Corte completo con lavado, masaje capilar y acabado con cera profesional.', 50, 1),
(4, 'Corte Infantil', 20.00, 'Corte especial para niños, cómodo y divertido.', 25, 1),
(5, 'Corte con Diseño', 45.00, 'Corte artístico con líneas o figuras personalizadas.', 45, 1),
(6, 'Afeitado Clásico', 30.00, 'Afeitado con toalla caliente, espuma artesanal y loción refrescante.', 25, 1),
(7, 'Perfilado de Barba', 25.00, 'Diseño y perfilado de barba con navaja y aceites esenciales.', 20, 1),
(8, 'Tinte de Barba', 35.00, 'Coloración natural para mantener una barba uniforme.', 30, 1),
(9, 'Masaje Capilar', 40.00, 'Masaje relajante con aceites esenciales y productos premium.', 30, 1),
(10, 'Exfoliación Facial', 45.00, 'Limpieza profunda para rejuvenecer la piel del rostro.', 40, 1),
(11, 'Tinte Capilar', 50.00, 'Coloración personalizada con productos de alta calidad.', 50, 1),
(12, 'Limpieza Facial Completa', 55.00, 'Tratamiento facial con vapor y mascarilla purificante.', 60, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `IdUsuarios` int(11) NOT NULL,
  `NombreUsuarios` varchar(50) NOT NULL,
  `ApellidoUsuarios` varchar(50) NOT NULL,
  `EmailUsuarios` varchar(255) NOT NULL,
  `ContrasenaUsuario` varchar(256) NOT NULL,
  `TelefonoUsuarios` varchar(9) NOT NULL,
  `DNIUsuarios` varchar(8) NOT NULL,
  `EstadoUsuarios` tinyint(1) NOT NULL DEFAULT 1,
  `FechaAltaUsuarios` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`IdUsuarios`, `NombreUsuarios`, `ApellidoUsuarios`, `EmailUsuarios`, `ContrasenaUsuario`, `TelefonoUsuarios`, `DNIUsuarios`, `EstadoUsuarios`, `FechaAltaUsuarios`) VALUES
(3, 'Dylan', 'Valladares', 'dylansecundaria123@gmail.com', '$2y$10$Q/zIXndxUdotcgtLqSVcAuiuej4f9jTV4zKmpin63mnOcHTikjcPi', '904088333', '87878793', 1, '2024-06-21 18:24:07'),
(6, 'Anayeli', 'Monzon', 'anayelinarvaez08@gmail.com', '$2y$10$Q/zIXndxUdotcgtLqSVcAuiuej4f9jTV4zKmpin63mnOcHTikjcPi', '987654321', '87654321', 1, '2024-06-22 01:18:51'),
(10, 'Elmer', 'Cabrera', 'pahablarwebadas@gmail.com', '$2y$10$Q/zIXndxUdotcgtLqSVcAuiuej4f9jTV4zKmpin63mnOcHTikjcPi', '999999999', '77777777', 1, '2025-10-12 18:09:13'),
(11, 'Usuario', 'Prueba', 'pahablarwebadas2@gmail.com', '$2y$10$MgLZZLIu5h.J3L..qZra1ufRT/.7BBeId7bmAKIANRbcOJZBG8nA6', '987654321', '12345678', 1, '2025-10-14 20:37:44'),
(9999, 'Cliente', 'Manual', 'mostrador@barberia.com', 'nopassword', '000000000', '00000000', 1, '2025-11-18 00:34:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `IdVentas` int(11) NOT NULL,
  `IdTransaccion` varchar(50) NOT NULL,
  `IdClientes` int(11) NOT NULL,
  `FechaVentas` datetime NOT NULL,
  `EstadoVentas` varchar(20) NOT NULL,
  `EmailVentas` varchar(255) NOT NULL,
  `TotalVentas` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`IdVentas`, `IdTransaccion`, `IdClientes`, `FechaVentas`, `EstadoVentas`, `EmailVentas`, `TotalVentas`) VALUES
(45, 'BARB5001TXC234567', 6, '2025-10-15 10:30:00', 'COMPLETED', 'anayelinarvaez08@gmail.com', 80.00),
(46, 'BARB5002TXD987654', 3, '2025-10-15 11:45:00', 'COMPLETED', 'dylansecundaria123@gmail.com', 105.00),
(47, 'BARB5003TXF112233', 6, '2025-10-15 14:00:00', 'COMPLETED', 'anayelinarvaez08@gmail.com', 150.00),
(48, 'BARB5004TXG445566', 3, '2025-10-15 16:15:00', 'COMPLETED', 'dylansecundaria123@gmail.com', 110.00),
(49, 'BARB5005TXH778899', 6, '2025-10-15 18:00:00', 'COMPLETED', 'anayelinarvaez08@gmail.com', 175.00),
(50, 'TRX-20251016-68f06f9680041', 10, '0000-00-00 00:00:00', 'COMPLETED', '', 615.00),
(51, 'pi_3SUJMnL0CpOiDccy198bwOzH', 10, '2025-11-16 22:44:41', 'COMPLETED', 'pahablarwebadas@gmail.com', 100.00),
(52, 'POS-1763444310', 9999, '2025-11-18 00:38:30', 'COMPLETED', 'Venta Mostrador', 365.00),
(53, 'pi_3SV2QDL0CpOiDccy1625DTwl', 10, '2025-11-18 22:51:17', 'COMPLETED', 'pahablarwebadas@gmail.com', 165.00),
(54, 'POS-1763524425', 9999, '2025-11-18 22:53:45', 'COMPLETED', 'Venta Mostrador', 420.00);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `administrador`
--
ALTER TABLE `administrador`
  ADD PRIMARY KEY (`IdAdministrador`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`IdCategorias`);

--
-- Indices de la tabla `detalleventas`
--
ALTER TABLE `detalleventas`
  ADD PRIMARY KEY (`IdDetalleVentas`),
  ADD KEY `IdVentas` (`IdVentas`),
  ADD KEY `IdProductos` (`IdProductos`);

--
-- Indices de la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`IdEmpleados`);

--
-- Indices de la tabla `imagenes`
--
ALTER TABLE `imagenes`
  ADD PRIMARY KEY (`IdImagen`);

--
-- Indices de la tabla `locales`
--
ALTER TABLE `locales`
  ADD PRIMARY KEY (`IdLocales`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`IdProductos`),
  ADD KEY `IdCategorias` (`IdCategorias`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`IdReservas`),
  ADD KEY `IdUsuarios` (`IdUsuarios`),
  ADD KEY `IdServicios` (`IdServicios`),
  ADD KEY `IdLocal` (`IdLocal`),
  ADD KEY `IdBarberos` (`IdBarberos`);

--
-- Indices de la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`IdServicios`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`IdUsuarios`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`IdVentas`),
  ADD KEY `IdClientes` (`IdClientes`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `administrador`
--
ALTER TABLE `administrador`
  MODIFY `IdAdministrador` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `IdCategorias` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `detalleventas`
--
ALTER TABLE `detalleventas`
  MODIFY `IdDetalleVentas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT de la tabla `empleados`
--
ALTER TABLE `empleados`
  MODIFY `IdEmpleados` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `imagenes`
--
ALTER TABLE `imagenes`
  MODIFY `IdImagen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `locales`
--
ALTER TABLE `locales`
  MODIFY `IdLocales` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `IdProductos` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `IdReservas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `IdServicios` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `IdUsuarios` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10000;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `IdVentas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalleventas`
--
ALTER TABLE `detalleventas`
  ADD CONSTRAINT `detalleventas_ibfk_1` FOREIGN KEY (`IdVentas`) REFERENCES `ventas` (`IdVentas`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detalleventas_ibfk_2` FOREIGN KEY (`IdProductos`) REFERENCES `productos` (`IdProductos`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`IdCategorias`) REFERENCES `categorias` (`IdCategorias`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`IdClientes`) REFERENCES `usuarios` (`IdUsuarios`) ON DELETE CASCADE ON UPDATE CASCADE;
