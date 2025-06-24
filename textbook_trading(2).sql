-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 23, 2025 at 02:38 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `textbook_trading`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `textbook_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `textbook_id`, `quantity`, `created_at`) VALUES
(19, 6, 11, 1, '2025-06-22 16:26:38'),
(20, 6, 17, 1, '2025-06-22 16:28:26'),
(21, 6, 16, 1, '2025-06-22 16:28:44'),
(22, 2, 2, 1, '2025-06-22 22:38:23');

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` int(11) NOT NULL,
  `user1_id` int(11) NOT NULL,
  `user2_id` int(11) NOT NULL,
  `textbook_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`id`, `user1_id`, `user2_id`, `textbook_id`, `created_at`) VALUES
(1, 2, 6, NULL, '2025-06-22 22:28:27');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `conversation_id`, `sender_id`, `content`, `created_at`, `is_read`) VALUES
(1, 1, 2, 'Hello, I\'m interested in your book: Complete Accounting for Cambridge IGCSE® & O Level', '2025-06-22 22:28:27', 1),
(2, 1, 2, 'hello my name is sethu', '2025-06-22 22:29:05', 1),
(3, 1, 2, 'i\'m 15', '2025-06-22 22:31:50', 1),
(4, 1, 6, 'is that so', '2025-06-22 22:34:44', 1),
(5, 1, 2, 'yeah lok in', '2025-06-22 22:34:53', 1),
(6, 1, 2, 'hey', '2025-06-23 12:20:11', 0);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `shipping_address` text NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('Pending','Processing','Shipped','Delivered','Cancelled') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_date`, `shipping_address`, `total_amount`, `status`) VALUES
(1, 6, '2025-06-22 15:58:51', '98 palmyr rd', 270.01, 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `textbook_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `seller_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `textbook_id`, `quantity`, `price`, `seller_id`) VALUES
(1, 1, 6, 1, 270.01, 6);

-- --------------------------------------------------------

--
-- Table structure for table `textbooks`
--

CREATE TABLE `textbooks` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `book_title` varchar(255) NOT NULL,
  `book_image` varchar(255) NOT NULL COMMENT 'Path to image file',
  `book_author` varchar(100) NOT NULL,
  `category` enum('Humanities','Engineering and the built environment','Health science','Commerce','Law','Science','Education') NOT NULL COMMENT 'Book subject category',
  `condition` enum('New','Used - Like New','Used - Good','Used - Acceptable') NOT NULL COMMENT 'Physical condition of the book',
  `Pick_up_Address` varchar(255) DEFAULT NULL,
  `negotiable` enum('yes','no') NOT NULL DEFAULT 'yes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `textbooks`
--

INSERT INTO `textbooks` (`id`, `user_id`, `description`, `price`, `created_at`, `book_title`, `book_image`, `book_author`, `category`, `condition`, `Pick_up_Address`, `negotiable`) VALUES
(2, 6, 'Fully mapped to the latest Cambridge syllabus, this rigorous and stretching approach strengthens foundations for Cambridge exam achievements, with support for the updated assessments. Prepare students for the transition to further study with plenty of enrichment material.', 786.00, '2025-06-17 22:09:58', 'Complete Accounting for Cambridge IGCSE® & O Level', 'acc-textbook.jpg', '', 'Commerce', 'New', 'claremont, Palmyra drive 45', 'yes'),
(6, 6, 'A serious need exists among students and others who have not previously come into contact with the basic principles of financial accounting. Basic Financial Accounting answers this need. The authors make no assumptions about the reader’s prior knowledge of financial accounting. Practical exercises at the end of each chapter allow the reader to test his or her own progress.', 270.01, '2025-06-17 22:43:26', 'Basic Financial Accounting', 'accounting-textbook.jpg', 'Unknown', 'Commerce', 'Used - Good', 'claremont, cavendish square 67', 'yes'),
(7, 6, 'uditing Fundamentals in a South African Context 2e is a practical, applied and engaging introductory textbook that supports students throughout the undergraduate level of the Auditing curriculum. The text is designed to enhance learning by supporting holistic understanding: theory is presented within the framework of the real-world business environment, assisting students to apply principles and standards with an understanding of their context.', 600.00, '2025-06-17 22:47:00', 'Auditing Fundimentals in South African context', 'auditing-textbook.jpg', 'Pieter von Wielligh', 'Commerce', 'Used - Acceptable', 'rayband, Kenelworth 56 street', 'yes'),
(8, 6, 'The text emphasises four themes to support evidence-based management decision making:\r\n1. Setting the statistical landscape in a management context\r\n2. Interpretative decision making based on patterns revealed by exploratory data analyses\r\n3. Statistical decision making guided by the test-based findings of inferential analyses\r\n4. Predictive decision making using statistical modelling evidence. The thread that links them is the role of data analytics as a management decision-support tool.', 680.00, '2025-06-17 22:50:27', 'Applied Business Statistics: Methods and Excel-Based Applications', 'busi_stats-textbook.jpg', 'Trevor Wegner ', 'Commerce', 'Used - Like New', 'claremont, cavendish square 67', 'yes'),
(9, 6, 'Introduction to Business Management 12e is a market-leading introductory level text which provides a contextual framework of the business environment and topical themes before delving into the management functions in more detail.', 539.00, '2025-06-17 22:53:23', 'Introduction to Business Management', 'business-textbook.jpg', '	 Erasmus, Rudansky-Kloppers, Strydom', 'Commerce', 'Used - Like New', 'claremont, cavendish square 67', 'yes'),
(10, 6, 'Like its predecessors, the sixth edition of Economics for South African students is a comprehensive introduction to economics in general, set against a contemporary South African background. The easy style and many practical examples make this publication extremely accessible. The book covers all the material usually prescribed for introductory courses, and it lays a solid foundation for intermediate and advanced studies in economics.\r\nThe sixth edition is a unique textbook. A number of experts have contributed short pieces under the collective title In the real world. We trust that these examples and case studies will be put to good use by lecturers (e.g. in discussion classes) while also providing students with more practical material to enhance their coursework.', 870.00, '2025-06-17 22:56:02', 'Economics for South African Students', 'economics-textbook.jpg', 'Philip Mohr', 'Commerce', 'Used - Like New', 'claremont, cavendish square 67', 'yes'),
(11, 6, 'This self-contained module for independent study covers the subjects most often needed by non-mathematics graduates, such as fundamental calculus, linear algebra, probability, and basic numerical methods. The easily-understandable text of Introduction to Actuarial and Mathematical Methods features examples, motivations, and lots of practice from a large number of end-of-chapter questions. For readers with diverse backgrounds entering programs of the Institute and Faculty of Actuaries, the Society of Actuaries, and the CFA Institute, Introduction to Actuarial and Mathematical Methods can provide a consistency of mathematical knowledge from the outset.', 3549.00, '2025-06-17 22:58:56', 'Introduction to Actuarial and Financial Mathematical Methods', 'financial-textbook.jpg', 'Stephen Garrett', 'Commerce', 'New', 'claremont, cavendish square 67', 'yes'),
(12, 6, 'Fundamental Accounting presents basic, yet essential knowledge required for first-year Financial Accounting courses at universities and universities of technology. This ninth edition, builds on the excellent foundations of previous editions, including updated legislative compliance chapters aligned to the Companies Act 71 of 2008 and IFRS updates to chapters and the questions that are affected by the discontinuation of cheques.', 655.00, '2025-06-17 23:01:34', 'Fundamental Accounting', 'fundemental-textbook.jpg', 'Ronald Arendse; Anna Coetzee', 'Commerce', 'Used - Good', 'claremont, cavendish square 67', 'yes'),
(13, 6, 'This seventh edition of Human Resource Management in South Africa provides a complete introduction and guide to Human Resource Management in the challenging and changing business world of modern South Africa. The many changes and events in both the external and internal environments for South African organisations in recent years have presented human resource managers with even greater challenges. Increasingly these managers, and the human resource function in general are being asked to make an even more significant contribution to the success of their organisations. This textbook will help you understand and handle the complexity, speed and magnitude of these changes and their impact on HR issues and policy, as well as how HR can, in turn, help South African organisations and their employees create value and achieve competitive advantage.', 1335.00, '2025-06-17 23:04:36', 'HUMAN RESOURCE MANAGEMENT IN SOUTH AFRICA', 'hr-textbook.jpg', 'Surette Wärnich, Michael R. Carrell, Norbert F. Elbert, Robert D. Hatfield  Cengage Learning', 'Commerce', 'Used - Acceptable', 'claremont, cavendish square 67', 'yes'),
(14, 6, 'Introduction to Financial Accounting has been written to address the theoretical aspects of accounting. The book has been written specifically for students who are studying Accounting 1.', 1280.00, '2025-06-17 23:07:56', 'Introduction to Financial Accounting', 'intro_to_finance-textbook.jpg', 'A Dempsey', 'Commerce', 'Used - Like New', 'claremont, cavendish square 67', 'yes'),
(15, 6, 'The established text Statistics for Management and Economics delivers an accessible and comprehensive overview for business students across the UK, Europe, the Middle East and Africa. With a wealth of examples and real data, this statistics textbook is essential reading for all business, management and economics courses at undergraduate and MBA level.', 1725.00, '2025-06-17 23:10:20', 'Statistics for Management and Economics', 'textbook#19.jpg', 'Gerald Keller', 'Commerce', 'New', 'claremont, cavendish square 67', 'yes'),
(16, 6, 'This book was written to ensure that students who did not take accounting at school and who will not major in accounting can understand and apply the basic principles and applications of accounting.', 1170.00, '2025-06-17 23:12:29', 'Accounting All-in-1 8th Edition', 'textbook13.jpg', 'Cornelius et al', 'Commerce', 'Used - Like New', 'claremont, cavendish square 67', 'yes'),
(17, 6, 'Principles of Management Accounting: A South African Perspective 3rd Edition is an accessible principles-and-concepts-based text aimed at undergraduate students of management accounting at universities and universities of technology. Sections integrating topics from groups of preceding chapters provide advanced reading for Honours and MBA level students. The book covers the management accounting syllabus of the South African Institute of Chartered Accountants (SAICA). In doing so, it also covers most aspects of the syllabi of the relevant papers of the Chartered Institute of Management Accountants (CIMA) and the Association of Chartered Accountants (ACCA).', 569.00, '2025-06-17 23:15:32', 'Principles of Management Accounting: A South African Perspective', 'textbook14.jpg', 'C. Cairney', 'Commerce', 'Used - Like New', 'claremont, cavendish square 67', 'yes'),
(18, 6, 'The 2nd edition of this textbook serves as an introductory text for attorneys working with accounting and the business valuation world. It is the textbook used for a law school course of a similar title.\r\n\r\nThe principles of accounting and finance directly extend to contract issues, torts, business and securities matters, taxation issues, partnership disputes, gift and estate matters, to name only a partial list. These areas of jurisprudence are often based significantly on substantive financial questions, and their measurement can be the heart of the entire matter. The application of broad accounting principles to countless business transactions requires an understanding of the objectives of financial reporting and the needs of the users of financial information.', 2559.00, '2025-06-17 23:17:41', 'Finance and Accounting for Lawyers, 2nd Edition', 'textbook15.jpg', 'Brian Peter Brinig', 'Commerce', 'New', 'claremont, cavendish square 67', 'yes'),
(19, 6, 'Offering a comprehensive introduction to the comparison of governments and political systems, this new edition helps students to understand not just the institutions and political cultures of their own countries but also those of a wide range of democracies and authoritarian regimes from around the world.', 869.00, '2025-06-17 23:19:59', 'Comparative Government and Politics', 'textbook16.jpg', 'John McCormick', 'Commerce', 'Used - Good', 'claremont, cavendish square 67', 'yes'),
(20, 6, 'The definitive guide to the history of economic thought fully revised twenty years after first publicationRoger Backhouses definitive guide takes the story of economic thinking from the ancient world to the present day with a brandnew chapter on the twentyfirst century and updates throughout to reflect the latest scholarship.Covering topics including globalisation inequality financial crises and the environment Backhouse brings his breadth of expertise and a contemporary lens to this original and insightful exploration of economics revealing how we got to where we are today.', 259.00, '2025-06-17 23:22:38', 'The Penguin History Of Economics', 'txtbook18.jpg', 'Roger E Backhouse', 'Commerce', 'New', 'claremont, cavendish square 67', 'yes');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `roles` enum('admin','student','seller') DEFAULT 'student',
  `student_number` varchar(50) DEFAULT NULL,
  `surname` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`, `roles`, `student_number`, `surname`) VALUES
(1, 'lerato123', 'lerato@student.university.edu', '$2y$10$W9t3L4.Y9kC5fYqU6KXhIuMqGkN/3c6U0v6tcJG5ZdTPtXKbwU5mK', '2025-06-06 21:15:09', 'student', NULL, NULL),
(2, 'sam_k', 'samk@university.edu', 'e10adc3949ba59abbe56e057f20f883e', '2025-06-06 21:15:09', 'student', 'Edu.vossie.67', NULL),
(3, 'ntombi77', 'ntombi77@campus.edu', '$2y$10$X2TTB9ukB9v45YZg7JErWu3o3HTFgq1Q35b7o9tWgZKVdo89chouW', '2025-06-06 21:15:09', 'student', NULL, NULL),
(4, 'karabo_45', 'karabo45@student.university.edu', 'e10adc3949ba59abbe56e057f20f883e', '2025-06-06 21:15:09', 'student', NULL, NULL),
(6, 'Shakes444', 'bester@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', '2025-06-10 10:53:21', 'seller', 'CT.Shakes', 'bester');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `textbook_id` (`textbook_id`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_conversation` (`user1_id`,`user2_id`,`textbook_id`),
  ADD KEY `user2_id` (`user2_id`),
  ADD KEY `textbook_id` (`textbook_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `conversation_id` (`conversation_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `textbook_id` (`textbook_id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexes for table `textbooks`
--
ALTER TABLE `textbooks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `textbooks`
--
ALTER TABLE `textbooks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`textbook_id`) REFERENCES `textbooks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `conversations`
--
ALTER TABLE `conversations`
  ADD CONSTRAINT `conversations_ibfk_1` FOREIGN KEY (`user1_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `conversations_ibfk_2` FOREIGN KEY (`user2_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `conversations_ibfk_3` FOREIGN KEY (`textbook_id`) REFERENCES `textbooks` (`id`);

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`textbook_id`) REFERENCES `textbooks` (`id`),
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `textbooks`
--
ALTER TABLE `textbooks`
  ADD CONSTRAINT `textbooks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
