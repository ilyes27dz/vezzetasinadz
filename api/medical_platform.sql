-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 26 juin 2025 à 01:27
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `medical_platform`
--

-- --------------------------------------------------------

--
-- Structure de la table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `date` date NOT NULL,
  `service_type` varchar(50) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `appointment_date` datetime DEFAULT NULL,
  `details` text DEFAULT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `appointments`
--

INSERT INTO `appointments` (`id`, `patient_id`, `doctor_id`, `date`, `service_type`, `service_id`, `appointment_date`, `details`, `status`, `created_at`) VALUES
(15, 11, 3, '2025-06-16', NULL, NULL, NULL, NULL, 'confirmed', '2025-06-15 08:32:49'),
(18, 11, 6, '2025-06-20', NULL, NULL, NULL, NULL, 'confirmed', '2025-06-18 14:29:05');

-- --------------------------------------------------------

--
-- Structure de la table `blood_donors`
--

CREATE TABLE `blood_donors` (
  `id` int(11) NOT NULL,
  `donor_name` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `blood_type` varchar(10) NOT NULL,
  `contact` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `blood_donors`
--

INSERT INTO `blood_donors` (`id`, `donor_name`, `city`, `blood_type`, `contact`, `details`, `is_active`, `created_at`) VALUES
(6, 'ilyes', 'حي السلام', 'AB+', 'fzfz', 'fzz', 1, '2025-06-02 19:17:13'),
(7, 'امين', 'مستغانم', 'O-', '045454545', 'صلامندر', 1, '2025-06-02 19:19:10');

-- --------------------------------------------------------

--
-- Structure de la table `blood_requests`
--

CREATE TABLE `blood_requests` (
  `id` int(11) NOT NULL,
  `patient_name` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `blood_type` varchar(10) DEFAULT NULL,
  `urgency` enum('حرجة','مستعجلة','عادية') DEFAULT NULL,
  `contact` varchar(100) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `blood_requests`
--

INSERT INTO `blood_requests` (`id`, `patient_name`, `city`, `blood_type`, `urgency`, `contact`, `details`, `created_at`, `is_active`) VALUES
(9, 'صحراوي', 'مستغانم', 'B+', 'حرجة', '0655382911', 'مستعجل من فضلكم', '2025-06-02 19:19:38', 1);

-- --------------------------------------------------------

--
-- Structure de la table `consultations`
--

CREATE TABLE `consultations` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `reply` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `reply_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `consultations`
--

INSERT INTO `consultations` (`id`, `patient_id`, `doctor_id`, `message`, `reply`, `created_at`, `reply_date`) VALUES
(6, 11, 3, 'allo dvdvdvd', 'bthtnt', '2025-06-15 09:33:18', '2025-06-15 09:37:36'),
(7, 11, 6, 'السلام عليكم طبيب \r\nلذي الام في الرأس ف الجهة الخلفية', 'منذ متى هذا الالم', '2025-06-18 15:41:46', '2025-06-18 15:42:18');

-- --------------------------------------------------------

--
-- Structure de la table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `sent_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `sent_at`) VALUES
(2, 'ilyes', 'ilyes.negh@gmail.com', 'ouii', 'meri', '2025-06-02 17:44:56');

-- --------------------------------------------------------

--
-- Structure de la table `distributors`
--

CREATE TABLE `distributors` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `distributors`
--

INSERT INTO `distributors` (`id`, `name`, `phone`, `city`) VALUES
(1, 'amine', '0655382911', 'mosta');

-- --------------------------------------------------------

--
-- Structure de la table `doctors`
--

CREATE TABLE `doctors` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `specialty_id` int(11) DEFAULT NULL,
  `wilaya` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT '',
  `work_hours` varchar(100) DEFAULT '',
  `city` varchar(50) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `verified` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `doctors`
--

INSERT INTO `doctors` (`id`, `user_id`, `specialty_id`, `wilaya`, `address`, `work_hours`, `city`, `bio`, `photo`, `verified`) VALUES
(3, 7, 1, 'مستغانم', 'hay salam', '09:00-17:00', NULL, 'bienvenue', '17487095942163.jpg', 0),
(4, 9, 5, 'مستغانم', '07 Ave Aissa Belkacem', '8:00-17:30', NULL, '', '17487293304751.png', 0),
(6, 13, 7, 'مستغانم', 'حي 05 جويلية مقابل مقر البلدية', '08:00-17:30', NULL, '', '17502565042271.jpg', 0);

-- --------------------------------------------------------

--
-- Structure de la table `drugs`
--

CREATE TABLE `drugs` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `drugs`
--

INSERT INTO `drugs` (`id`, `name`, `price`) VALUES
(1, 'rapidus', 205);

-- --------------------------------------------------------

--
-- Structure de la table `drug_orders`
--

CREATE TABLE `drug_orders` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `pharmacy_id` int(11) NOT NULL,
  `drug_name` varchar(255) NOT NULL,
  `notes` text DEFAULT NULL,
  `prescription_file` varchar(255) DEFAULT NULL,
  `delivery` enum('pickup','delivery') DEFAULT 'pickup',
  `price` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `status` enum('جديد','تم الاستقبال','جاري التحضير','تم الإرسال','تم الوصول','ملغى') NOT NULL DEFAULT 'جديد',
  `notified_patient` tinyint(1) NOT NULL DEFAULT 1,
  `notified_pharmacy` tinyint(1) NOT NULL DEFAULT 1,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `distributor_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `drug_orders`
--

INSERT INTO `drug_orders` (`id`, `patient_id`, `pharmacy_id`, `drug_name`, `notes`, `prescription_file`, `delivery`, `price`, `created_at`, `status`, `notified_patient`, `notified_pharmacy`, `phone`, `email`, `distributor_id`) VALUES
(9, NULL, 1, 'RAPIDUS', '', NULL, 'pickup', 512, '2025-06-01 19:29:36', 'تم الإرسال', 1, 1, '0655282828', 'aya@gmail.com', NULL),
(10, NULL, 1, 'voltaran', '', NULL, 'pickup', 250, '2025-06-01 19:40:17', 'تم الإرسال', 1, 1, '0655282828', 'ilyes.negh@gmail.com', NULL),
(12, NULL, 1, 'voltaran', '', NULL, 'pickup', 250, '2025-06-01 23:51:30', 'تم الإرسال', 1, 1, '0655282828', 'ilyes.negh@gmail.com', 1),
(13, 10, 1, 'voltaran', '', 'presc_683e28c9634ce.jpeg', 'delivery', 550, '2025-06-02 23:42:17', 'جديد', 1, 1, '0655382911', NULL, NULL),
(14, NULL, 1, 'voltaran', '', NULL, 'delivery', 550, '2025-06-02 23:50:52', 'تم الإرسال', 1, 1, '0655382911', 'ilyy.dzz@gmail.com', 1),
(15, 11, 1, 'RAPIDUS', 'yjyj', 'presc_684e858bb0bdb.png', 'delivery', 812, '2025-06-15 09:34:19', 'تم الوصول', 1, 1, '0655382911', NULL, 1);

-- --------------------------------------------------------

--
-- Structure de la table `medical_ads`
--

CREATE TABLE `medical_ads` (
  `id` int(11) NOT NULL,
  `title` varchar(150) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `target_id` int(11) DEFAULT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `pharmacy_drugs`
--

CREATE TABLE `pharmacy_drugs` (
  `id` int(11) NOT NULL,
  `pharmacy_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` int(11) NOT NULL,
  `is_available` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `pharmacy_drugs`
--

INSERT INTO `pharmacy_drugs` (`id`, `pharmacy_id`, `name`, `price`, `is_available`) VALUES
(1, 1, 'RAPIDUS', 512, 1),
(2, 1, 'voltaran', 250, 1),
(3, 1, 'doliprane', 150, 1);

-- --------------------------------------------------------

--
-- Structure de la table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `doctor_notes` text DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `auto_renew` tinyint(4) DEFAULT 0,
  `last_renewed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `prescriptions`
--

INSERT INTO `prescriptions` (`id`, `patient_id`, `doctor_id`, `notes`, `doctor_notes`, `file`, `created_at`, `auto_renew`, `last_renewed_at`) VALUES
(18, 11, 6, 'وصفة مرض الخاص بيه من فضلك', NULL, 'presc_1750257512_876.pdf', '2025-06-18 15:32:44', 1, NULL),
(19, 11, 6, 'وصفة خاصة بالشهر الجديد', 'يرجى الالتزام بجميع التبيهات', '1750257784_ordonance.pdf', '2025-06-18 15:43:04', 1, NULL),
(20, 11, 6, 'وصفة خاصة بالشهر الجديد', NULL, NULL, '2025-06-18 15:52:03', 1, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `prescription_renew_requests`
--

CREATE TABLE `prescription_renew_requests` (
  `id` int(11) NOT NULL,
  `prescription_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `requested_at` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `response_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `prescription_renew_requests`
--

INSERT INTO `prescription_renew_requests` (`id`, `prescription_id`, `patient_id`, `doctor_id`, `requested_at`, `status`, `response_at`) VALUES
(10, 19, 11, 6, '2025-06-18 15:48:58', 'pending', NULL),
(11, 20, 11, 6, '2025-06-18 15:52:03', 'pending', NULL),
(12, 18, 11, 6, '2025-06-18 15:56:20', 'pending', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `rare_drugs`
--

CREATE TABLE `rare_drugs` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `added_at` datetime NOT NULL DEFAULT current_timestamp(),
  `pharmacy_id` int(11) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `rare_drugs`
--

INSERT INTO `rare_drugs` (`id`, `name`, `category`, `notes`, `added_at`, `pharmacy_id`, `photo`) VALUES
(1, 'lovinox', 'الدم', '', '2025-06-18 16:38:18', 1, 'rare_drug_6852e218c53ba.webp');

-- --------------------------------------------------------

--
-- Structure de la table `rare_drug_requests`
--

CREATE TABLE `rare_drug_requests` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `drug_id` int(11) NOT NULL,
  `pharmacy_id` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `prescription_file` varchar(255) DEFAULT NULL,
  `requested_at` datetime NOT NULL,
  `status` varchar(50) DEFAULT 'جديد'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `rare_drug_requests`
--

INSERT INTO `rare_drug_requests` (`id`, `patient_id`, `drug_id`, `pharmacy_id`, `notes`, `phone`, `prescription_file`, `requested_at`, `status`) VALUES
(1, 11, 1, 1, 'هدا الدواء من فضلك مستعجل', '0655382911', 'rare_presc_6852ddb42a909.jpg', '2025-06-18 16:39:32', 'تم الوصول'),
(2, NULL, 1, 1, 'gtktkt', '0655382911', NULL, '2025-06-18 17:34:31', 'جديد');

-- --------------------------------------------------------

--
-- Structure de la table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('pharmacy','lab','ambulance','hospital','imaging','psychologist','orthophonist','physical','other') NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `wilaya` varchar(50) DEFAULT NULL,
  `work_hours` varchar(100) DEFAULT '',
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `services`
--

INSERT INTO `services` (`id`, `name`, `type`, `address`, `phone`, `city`, `wilaya`, `work_hours`, `photo`, `created_at`) VALUES
(1, 'صيدلية البركة', 'pharmacy', 'حي السلام ', '045454545', 'مستغانم', 'مستغانم', '', '', '2025-05-31 10:51:52'),
(4, 'مخبر تخاليل البركة', 'lab', 'حي السلاام', '045454545', NULL, 'مستغانم', '09:00-00:00', '17487899366463.png', '2025-06-01 14:58:56'),
(5, 'صحراوي صفية', 'psychologist', 'صلامندر-', '0660606060', NULL, 'مستغانم', '09:00-17:00', '17487975538247.jpg', '2025-06-01 17:05:53'),
(6, 'سيارة الإسعاف البركة', 'ambulance', 'مستغانم', '045454545', NULL, 'مستغانم', '24/24', '17487976278871.jpg', '2025-06-01 17:07:07'),
(7, 'مخبر الأشغة السلام', 'imaging', 'مستفانم', '045454545', NULL, 'مستغانم', '08:00-22:00', '17487976808002.jpg', '2025-06-01 17:08:00'),
(8, 'المستشفى الحامعي', 'hospital', 'مستغانم', '045454545', NULL, 'مستغانم', '24/24', '17487977587081.jpg', '2025-06-01 17:09:18'),
(9, 'خديجة برحو', 'orthophonist', 'حي 300 مسكن', '0770070000', NULL, 'مستغانم', '8:00-17:30', '17487978239123.jpg', '2025-06-01 17:10:23'),
(11, 'صيدلية السلامة', 'pharmacy', 'مستغانم', '045454545', NULL, 'مستغانم', '09:00-17:00', '17487979527820.jpg', '2025-06-01 17:12:32'),
(12, 'الكيني EL KINIE', 'physical', 'Sidi Lakhdar', '045454545', NULL, 'MOSTAGANEM', '08:00-17:30', '17495490511076.jpg', '2025-06-10 09:50:51');

-- --------------------------------------------------------

--
-- Structure de la table `specialties`
--

CREATE TABLE `specialties` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `specialties`
--

INSERT INTO `specialties` (`id`, `name`) VALUES
(1, 'طب الأسنان'),
(2, 'جراحة عامة'),
(4, 'طب عام'),
(5, 'طب العيون'),
(6, 'طب العظام'),
(7, 'طب الأعصاب');

-- --------------------------------------------------------

--
-- Structure de la table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plan` varchar(32) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `payment_method` varchar(100) DEFAULT NULL,
  `payment_ref` varchar(100) DEFAULT NULL,
  `status` enum('pending','active','expired','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `allowed_features` int(11) DEFAULT 0,
  `used_features` int(11) DEFAULT 0,
  `allowed_appointments` int(11) DEFAULT 0,
  `used_appointments` int(11) DEFAULT 0,
  `allowed_consultations` int(11) DEFAULT 0,
  `used_consultations` int(11) DEFAULT 0,
  `allowed_prescriptions` int(11) DEFAULT 0,
  `used_prescriptions` int(11) DEFAULT 0,
  `allowed_ads` int(11) DEFAULT 0,
  `used_ads` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `user_id`, `plan`, `start_date`, `end_date`, `payment_method`, `payment_ref`, `status`, `created_at`, `allowed_features`, `used_features`, `allowed_appointments`, `used_appointments`, `allowed_consultations`, `used_consultations`, `allowed_prescriptions`, `used_prescriptions`, `allowed_ads`, `used_ads`) VALUES
(7, 7, '6 أشهر', '2025-06-18', '2025-12-18', 'بطاقة ذهبية', '2888888888888888', 'active', '2025-06-18 14:02:46', 0, 0, 200, 0, 350, 0, 150, 0, 15, 0),
(9, 11, '6 أشهر', '2025-06-18', '2025-12-18', 'بطاقة ذهبية', '1234567899999999', 'active', '2025-06-18 14:28:21', 0, 0, 30, 1, 40, 1, 10, 2, 0, 0);

-- --------------------------------------------------------

--
-- Structure de la table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `approved` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `role` enum('admin','doctor','patient','pharmacy') NOT NULL,
  `pharmacy_id` int(11) DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `fullname` varchar(100) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `photo`, `role`, `pharmacy_id`, `status`, `created_at`, `fullname`, `avatar`) VALUES
(1, 'ilyes', 'safiya.sahraoui@gmail.com', '$2y$10$jzxvhfKUZMAAIMKjOIQlAO2rb0TCQbbekWIt5QkYD3U2WhL80eXve', '123456', NULL, 'admin', NULL, 1, '2025-05-31 10:20:35', NULL, NULL),
(7, 'safiya', 'ayloul27@gmail.com', '$2y$10$Gfo/BtNoWbZ62VGRPSudbOurPk6M5qCq9F9gzSvtT4.F9lPflhiXW', '045454545', NULL, 'doctor', NULL, 1, '2025-05-31 12:58:46', NULL, NULL),
(8, '', 'baraka@gmail.com', '$2y$10$9waJ8rTqM6COSW2i.X/.6OeNOWISn4DVGEVpJkTdNS661y6cVcXiW', '0655282828', NULL, 'pharmacy', 1, 1, '2025-05-31 14:17:00', 'ilyes', 'pharm_683b1b51b2fa1.png'),
(9, 'دكتورة عائشة بوسماحة', 'aicha@gmail.com', '$2y$10$cCVDWa2KxxQDl9CiuTReVu/XbmhSDO7gAAZxijEqthvhbY4/DyU22', '045454545', NULL, 'doctor', NULL, 1, '2025-05-31 22:08:50', NULL, NULL),
(10, 'Ilyes', 'ilyy.dzz@gmail.com', '$2y$10$HBKBr30.iavwhOKEqaCPteib9oCi9ci5ibh8nRqQ9XRDHOQFwUU62', '0655382911', '', 'patient', NULL, 1, '2025-06-02 22:41:02', NULL, NULL),
(11, 'safiya', 'ilyes.negh@gmail.com', '$2y$10$eHcx3zoPvULVet6RWmps2.tywTPGuMeM77hRnJeXyNZFa8VWfHJKq', '06553829414', '17502568576991.png', 'patient', NULL, 1, '2025-06-15 08:32:15', NULL, NULL),
(13, 'صحراوي محمد', 'MED27@gmail.com', '$2y$10$7a5.qtiKw/ub0kHN3M5IR.rJunb01GyYLLItOyCLIT7QXc4GSr3ui', '0655382911', NULL, 'doctor', NULL, 1, '2025-06-18 14:21:44', NULL, NULL);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Index pour la table `blood_donors`
--
ALTER TABLE `blood_donors`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `blood_requests`
--
ALTER TABLE `blood_requests`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `consultations`
--
ALTER TABLE `consultations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Index pour la table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `distributors`
--
ALTER TABLE `distributors`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `drugs`
--
ALTER TABLE `drugs`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `drug_orders`
--
ALTER TABLE `drug_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `pharmacy_id` (`pharmacy_id`);

--
-- Index pour la table `medical_ads`
--
ALTER TABLE `medical_ads`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Index pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `pharmacy_drugs`
--
ALTER TABLE `pharmacy_drugs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pharmacy_id` (`pharmacy_id`);

--
-- Index pour la table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Index pour la table `prescription_renew_requests`
--
ALTER TABLE `prescription_renew_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prescription_id` (`prescription_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `prescription_renew_requests_ibfk_3` (`doctor_id`);

--
-- Index pour la table `rare_drugs`
--
ALTER TABLE `rare_drugs`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `rare_drug_requests`
--
ALTER TABLE `rare_drug_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `drug_id` (`drug_id`);

--
-- Index pour la table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `specialties`
--
ALTER TABLE `specialties`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT pour la table `blood_donors`
--
ALTER TABLE `blood_donors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `blood_requests`
--
ALTER TABLE `blood_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `consultations`
--
ALTER TABLE `consultations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `distributors`
--
ALTER TABLE `distributors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `drugs`
--
ALTER TABLE `drugs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `drug_orders`
--
ALTER TABLE `drug_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `medical_ads`
--
ALTER TABLE `medical_ads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT pour la table `pharmacy_drugs`
--
ALTER TABLE `pharmacy_drugs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT pour la table `prescription_renew_requests`
--
ALTER TABLE `prescription_renew_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `rare_drugs`
--
ALTER TABLE `rare_drugs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `rare_drug_requests`
--
ALTER TABLE `rare_drug_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `specialties`
--
ALTER TABLE `specialties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `consultations`
--
ALTER TABLE `consultations`
  ADD CONSTRAINT `consultations_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `doctors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `drug_orders`
--
ALTER TABLE `drug_orders`
  ADD CONSTRAINT `drug_orders_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `drug_orders_ibfk_2` FOREIGN KEY (`pharmacy_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `pharmacy_drugs`
--
ALTER TABLE `pharmacy_drugs`
  ADD CONSTRAINT `pharmacy_drugs_ibfk_1` FOREIGN KEY (`pharmacy_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prescriptions_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `prescription_renew_requests`
--
ALTER TABLE `prescription_renew_requests`
  ADD CONSTRAINT `prescription_renew_requests_ibfk_1` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`id`),
  ADD CONSTRAINT `prescription_renew_requests_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `prescription_renew_requests_ibfk_3` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `rare_drug_requests`
--
ALTER TABLE `rare_drug_requests`
  ADD CONSTRAINT `rare_drug_requests_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rare_drug_requests_ibfk_2` FOREIGN KEY (`drug_id`) REFERENCES `rare_drugs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
