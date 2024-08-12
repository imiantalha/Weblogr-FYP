-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 13, 2024 at 10:34 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `weblogr`
--

-- --------------------------------------------------------

--
-- Table structure for table `blogs`
--

CREATE TABLE `blogs` (
  `blog_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `description` text DEFAULT NULL,
  `category` varchar(15) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `likes` int(11) DEFAULT 0,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `blogs`
--

INSERT INTO `blogs` (`blog_id`, `title`, `created_date`, `description`, `category`, `image`, `likes`, `user_id`) VALUES
(1, 'Education', '2024-05-10 10:49:21', 'Education is the transmission of knowledge, skills, and character traits and manifests in various forms. Formal education occurs within a structured institutional framework, such as public schools, following a curriculum.', 'education', 'education.PNG', 4, 2),
(2, 'Sports', '2024-05-10 10:39:44', 'An activity involving physical exertion and skill in which an individual or team competes against another or others for entertainment.', 'sport', 'sports.PNG', 2, 2),
(3, 'Fashion', '2024-05-10 10:42:06', 'Fashion is the most general term and applies to any way of dressing, behaving, writing, or performing that is favored at any one time or place. the current fashion.', 'fashion', 'fashion.PNG', 4, 2),
(4, 'Food', '2024-05-10 10:42:55', 'Any nutritious substance that people or animals eat or drink or that plants absorb in order to maintain life and growth.', 'food', 'food.PNG', 2, 2),
(5, 'Travel', '2024-05-01 00:00:00', 'Travel is the movement of people between distant geographical locations. Travel can be done by foot, bicycle, automobile, train, boat, bus, airplane, ship or other means, with or without luggage, and can be one way or round trip.', 'travel', 'travel.PNG', 0, 2),
(6, 'Technology', '2024-05-01 01:00:00', 'Technology is the application of conceptual knowledge for achieving practical goals, especially in a reproducible way.', 'technology', 'technology.PNG', 0, 2),
(7, 'Travel', '2024-05-01 02:00:00', 'Travel is something you buy make you richer.', 'travel', 'pic3.PNG', 0, 2),
(8, 'Education', '2024-05-10 10:20:45', 'Education is both the act of teaching knowledge to others and the act of receiving knowledge from someone else.', 'education', 'education1.PNG', 2, 2),
(9, 'Travel Types', '2024-05-10 10:39:17', 'Some additional travel types include luxury travel, backpacking, road trips, volunteer travel, educational travel, medical tourism, religious tourism, pilgrimage travel, and honeymoon travel.', 'travel', 'travel1.PNG', 3, 2),
(10, 'Fashion', '2024-05-08 06:33:14', 'Fashion is most often thought of as a global industry that is invested in anticipating what we wear and how we wish to appear to others.', 'fashion', 'fashion1.PNG', 4, 2),
(11, 'Sports', '2024-05-10 10:22:29', 'Sport is a form of physical activity or game. Often competitive and organized, sports use, maintain, or improve physical ability and skills.', 'sport', 'sports1.PNG', 5, 2);

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL,
  `blog_id` int(11) DEFAULT NULL,
  `commenter_id` int(11) DEFAULT NULL,
  `comment_text` text DEFAULT NULL,
  `likes` int(11) NOT NULL,
  `comment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`comment_id`, `blog_id`, `commenter_id`, `comment_text`, `likes`, `comment_date`) VALUES
(13, 9, 3, 'Travel', 4, '2024-05-04 17:24:05'),
(14, 11, 3, 'sports', 5, '2024-05-04 19:07:57'),
(16, 9, 3, 'Travel is something amazing.', 1, '2024-05-05 18:06:42'),
(26, 11, 3, 'My favorite', 1, '2024-05-05 18:50:08'),
(27, 1, 2, 'Education is key to success.', 1, '2024-05-07 06:36:49'),
(30, 3, 2, 'fashion is somthing...', 1, '2024-05-07 06:48:28'),
(31, 6, 2, 'Technolory is changing the world', 1, '2024-05-07 07:32:55'),
(32, 10, 2, 'fashion is some..', 2, '2024-05-07 07:35:49'),
(33, 5, 3, 'I like it.', 0, '2024-05-07 07:37:21'),
(34, 4, 2, 'Always try healthy food.', 2, '2024-05-07 07:53:34'),
(35, 2, 3, 'I like to play cricket', 2, '2024-05-07 08:04:05'),
(37, 2, 2, 'Sports..', 0, '2024-05-10 10:11:16');

-- --------------------------------------------------------

--
-- Table structure for table `draft_posts`
--

CREATE TABLE `draft_posts` (
  `draft_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `created_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category` varchar(15) NOT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `followers`
--

CREATE TABLE `followers` (
  `id` int(11) NOT NULL,
  `blogger_id` int(11) DEFAULT NULL,
  `follower_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `followers`
--

INSERT INTO `followers` (`id`, `blogger_id`, `follower_id`) VALUES
(1, 2, 2),
(2, 2, 4),
(3, 2, 3);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `content` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `content`, `user_id`) VALUES
(19, 'TALHA posted a new post.', 4),
(23, 'TALHA posted a new post.', 4),
(24, 'TALHA posted a new post.', 3),
(26, 'TALHA likes your comment <br> \'sports\'', 3),
(27, 'TALHA likes your comment <br> \'sports\'', 3),
(28, 'ADMIN likes your comment <br> \'sports\'', 3),
(30, 'TALHA likes your comment <br> \'Travel is something amazing.\'', 3),
(32, 'TALHA likes your comment <br> \'I like to play cricket\'', 3),
(33, 'TALHA likes your comment <br> \'I like to play cricket\'', 3),
(48, 'TALHA posted a new post.', 4),
(49, 'TALHA posted a new post.', 3),
(50, 'TALHA posted a new post.', 2),
(51, 'TALHA posted a new post.', 4),
(52, 'TALHA posted a new post.', 3);

-- --------------------------------------------------------

--
-- Table structure for table `profile`
--

CREATE TABLE `profile` (
  `profile_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `profile`
--

INSERT INTO `profile` (`profile_id`, `user_id`, `full_name`, `profile_picture`, `bio`, `created_at`, `updated_at`) VALUES
(4, 2, 'Muhammad Talha', 'Talha1.PNG', 'I\'m a student.', '2024-04-30 05:31:56', '2024-05-08 13:09:44'),
(5, 3, 'Admin', '', 'I am Admin', '2024-04-30 07:14:08', '2024-04-30 07:19:39');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `blog_id` int(11) DEFAULT NULL,
  `blogger_id` int(11) DEFAULT NULL,
  `reporter_id` int(11) DEFAULT NULL,
  `content` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `fullname` varchar(25) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `otp` varchar(10) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_verified` tinyint(1) DEFAULT 0,
  `user_type` enum('Common user','Admin') DEFAULT 'Common user'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `fullname`, `username`, `email`, `password`, `otp`, `date`, `is_verified`, `user_type`) VALUES
(2, 'Muhammad Talha', 'talha', 'talhaarshad427@gmail.com', '$2y$10$y4tUyBgoJeZSz5oSCiVckOncRlqmEIzFRLkeoQcQhF4U9O2z7b2BK', '819707', '2024-04-25 19:35:32', 1, 'Common user'),
(3, 'Admin', 'admin', 'admin@gmail.com', '$2y$10$X4ELLj.24fGGB6mNogYqFO6woelBUogNeGwp3Co2S0LmLVdd/UTO.', '604551', '2024-04-26 10:00:18', 1, 'Admin'),
(4, 'Muhammad Ali', 'ali', 'ali@gmail.com', '$2y$10$/i2WJTj5bsocsP3EhKFJ1eRWDUm0oaxiPUSYU1a1M/AS0z1hlsxX.', '978936', '2024-05-01 20:31:40', 1, 'Common user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `blogs`
--
ALTER TABLE `blogs`
  ADD PRIMARY KEY (`blog_id`),
  ADD KEY `fk_user_id` (`user_id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `blog_id` (`blog_id`),
  ADD KEY `commenter_id` (`commenter_id`);

--
-- Indexes for table `draft_posts`
--
ALTER TABLE `draft_posts`
  ADD PRIMARY KEY (`draft_id`),
  ADD KEY `fk_user_id` (`user_id`);

--
-- Indexes for table `followers`
--
ALTER TABLE `followers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `blogger_id` (`blogger_id`),
  ADD KEY `follower_id` (`follower_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `profile`
--
ALTER TABLE `profile`
  ADD PRIMARY KEY (`profile_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`);


--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `blogs`
--
ALTER TABLE `blogs`
  MODIFY `blog_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `draft_posts`
--
ALTER TABLE `draft_posts`
  MODIFY `draft_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `followers`
--
ALTER TABLE `followers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `profile`
--
ALTER TABLE `profile`
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;


--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`blog_id`) REFERENCES `blogs` (`blog_id`),
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`commenter_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `draft_posts`
--
ALTER TABLE `draft_posts`
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `followers`
--
ALTER TABLE `followers`
  ADD CONSTRAINT `followers_ibfk_1` FOREIGN KEY (`blogger_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `followers_ibfk_2` FOREIGN KEY (`follower_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `profile`
--
ALTER TABLE `profile`
  ADD CONSTRAINT `profile_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
