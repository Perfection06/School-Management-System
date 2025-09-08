-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 08, 2025 at 10:57 AM
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
-- Database: `reliance`
--

-- --------------------------------------------------------

--
-- Table structure for table `accountant`
--

CREATE TABLE `accountant` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `postal_address` text DEFAULT NULL,
  `ethnicity` varchar(50) DEFAULT NULL,
  `nic_number` varchar(12) NOT NULL,
  `marital_status` varchar(20) DEFAULT NULL,
  `whatsapp_number` varchar(15) DEFAULT NULL,
  `residence_number` varchar(15) DEFAULT NULL,
  `first_language` varchar(50) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `position` varchar(50) DEFAULT 'Accountant',
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `accountant_block_reasons`
--

CREATE TABLE `accountant_block_reasons` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `block_reason` text NOT NULL,
  `block_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'Najad', '$2y$10$KXrqOEEIPrPlWvtldNYFkORl5/OEKNjD7MCrWYudUPCGu/fNbayxm', '2024-11-28 00:44:48'),
(4, 'Reliance', '$2y$10$j2cGN1toQWML7V1Z.U6rP.DrMdh8x6J0NaApeQ9yUWOPXmCfnHS0y', '2024-12-22 15:45:37'),
(5, 'Reliance2', '$2y$10$WhDv5l/DtOFGIPi41o7HxOoWhPmTxnRedfdXKWVHLYrRmk6Zy8dPa', '2024-12-23 03:03:00');

-- --------------------------------------------------------

--
-- Table structure for table `al_result_accountant`
--

CREATE TABLE `al_result_accountant` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `subject_name` varchar(100) DEFAULT NULL,
  `result` varchar(5) DEFAULT NULL,
  `index_number` varchar(20) DEFAULT NULL,
  `year` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `al_result_staff`
--

CREATE TABLE `al_result_staff` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `subject_name` varchar(100) DEFAULT NULL,
  `result` enum('A','B','C','S','W') NOT NULL,
  `index_number` varchar(50) NOT NULL,
  `year` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `al_result_staff`
--

INSERT INTO `al_result_staff` (`id`, `username`, `subject_name`, `result`, `index_number`, `year`) VALUES
(1, 'staff', 'asd', 'A', '20202022', 2003),
(2, 'staff', 'asd', 'A', '20202022', 2003),
(3, 'staff', 'asd', 'A', '20202022', 2003),
(4, 'staff', 'asd', 'B', '20202022', 2003);

-- --------------------------------------------------------

--
-- Table structure for table `al_result_teacher`
--

CREATE TABLE `al_result_teacher` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `subject_name` varchar(100) DEFAULT NULL,
  `result` enum('A','B','C','S','W') NOT NULL,
  `index_number` varchar(50) NOT NULL,
  `year` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `al_result_teacher`
--

INSERT INTO `al_result_teacher` (`id`, `username`, `subject_name`, `result`, `index_number`, `year`) VALUES
(5, 'teacher', 'asd', 'B', '2020202', 2005),
(6, 'teacher', 'asd', 'B', '2020202', 2005),
(7, 'teacher', 'asd', 'A', '2020202', 2005),
(8, 'teacher', 'asd', 'A', '2020202', 2005),
(9, 'subteacher', 'asd', 'A', '2020202', 2004),
(10, 'subteacher', 'asd', 'A', '2020202', 2004),
(11, 'subteacher', 'asd', 'A', '2020202', 2004),
(12, 'subteacher', 'asd', 'A', '2020202', 2004);

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `end_date` date NOT NULL,
  `username` varchar(50) NOT NULL,
  `class_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assign_payment`
--

CREATE TABLE `assign_payment` (
  `id` int(11) NOT NULL,
  `fee_per_month` decimal(10,2) NOT NULL,
  `discount_per_month` decimal(10,2) NOT NULL,
  `grade` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `teacher_user_id` varchar(50) NOT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('Present','Absent','Late') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `block_reasons`
--

CREATE TABLE `block_reasons` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `block_reason` text NOT NULL,
  `block_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chapters`
--

CREATE TABLE `chapters` (
  `id` int(11) NOT NULL,
  `chapter_name` varchar(255) NOT NULL,
  `periods_allocated` int(11) NOT NULL,
  `term` varchar(50) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `grade_id` int(11) NOT NULL,
  `completion_status` tinyint(1) DEFAULT 0,
  `finished_on_time` tinyint(1) DEFAULT NULL,
  `extra_periods` int(11) DEFAULT 0,
  `reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `educational_details`
--

CREATE TABLE `educational_details` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `other_educational_qualification` text DEFAULT NULL,
  `professional_qualification` text DEFAULT NULL,
  `extra_curricular_activities` text DEFAULT NULL,
  `work_experience` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `educational_details`
--

INSERT INTO `educational_details` (`id`, `username`, `other_educational_qualification`, `professional_qualification`, `extra_curricular_activities`, `work_experience`) VALUES
(2, 'teacher', 'skldalsjdajlsdsssssss', 'askdjlasjdlasjdssssss', 'askdjaksjdlaskjdssssss', 'kasjdlkajsldalsdssssssss'),
(3, 'subteacher', 'asdasdasd', 'asdasdasd', 'asdasd', 'asdasd'),
(4, 'staff', 'asdasdasdasdasd', 'asdasdasdad', 'asdasasdasd', 'asdasdadasd');

-- --------------------------------------------------------

--
-- Table structure for table `educational_details_accountant`
--

CREATE TABLE `educational_details_accountant` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `other_educational_qualification` text DEFAULT NULL,
  `professional_qualification` text DEFAULT NULL,
  `extra_curricular_activities` text DEFAULT NULL,
  `work_experience` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--

CREATE TABLE `exams` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `term` int(11) NOT NULL,
  `grade_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `publish_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exam_subjects`
--

CREATE TABLE `exam_subjects` (
  `id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `exam_date` date NOT NULL,
  `exam_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedbacks`
--

CREATE TABLE `feedbacks` (
  `id` int(11) NOT NULL,
  `teacher_username` varchar(50) NOT NULL,
  `grade_id` int(11) NOT NULL,
  `feedback` text NOT NULL,
  `feedback_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fee_registration`
--

CREATE TABLE `fee_registration` (
  `id` int(11) NOT NULL,
  `student_username` varchar(100) NOT NULL,
  `student_name` varchar(255) NOT NULL,
  `parent_phone` varchar(15) NOT NULL,
  `class` varchar(50) NOT NULL,
  `months_paid` text NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `discount_received` decimal(10,2) DEFAULT NULL,
  `final_amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `receipt_no` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gallery_images`
--

CREATE TABLE `gallery_images` (
  `id` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `grade_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`id`, `grade_name`) VALUES
(1, 'Grade 1');

-- --------------------------------------------------------

--
-- Table structure for table `grade_subject`
--

CREATE TABLE `grade_subject` (
  `grade_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grade_subject`
--

INSERT INTO `grade_subject` (`grade_id`, `subject_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_username` varchar(50) NOT NULL,
  `receiver_username` varchar(50) DEFAULT NULL,
  `content` text NOT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `is_broadcast` tinyint(1) DEFAULT 0,
  `target_group` enum('Students','Teachers','NoClass_Teachers','Staff') DEFAULT NULL,
  `grade_id` int(11) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0,
  `user_sender` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `noclass_teacher`
--

CREATE TABLE `noclass_teacher` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `date_of_birth` date NOT NULL,
  `postal_address` text NOT NULL,
  `ethnicity` varchar(255) DEFAULT NULL,
  `nic_number` varchar(50) NOT NULL,
  `marital_status` enum('Single','Married') NOT NULL,
  `whatsapp_number` varchar(15) DEFAULT NULL,
  `residence_number` varchar(15) DEFAULT NULL,
  `first_language` varchar(50) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `teaching_classes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`teaching_classes`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `noclass_teacher`
--

INSERT INTO `noclass_teacher` (`id`, `username`, `full_name`, `gender`, `date_of_birth`, `postal_address`, `ethnicity`, `nic_number`, `marital_status`, `whatsapp_number`, `residence_number`, `first_language`, `profile_image`, `subject_id`, `teaching_classes`) VALUES
(1, 'subteacher', 'Sub Teacher', 'Male', '2025-08-31', '123 Sri Lanka', 'Sri Lankan', '222222222222222222', 'Single', '0777111666', '07775252232', 'English', '', 1, '[\"1\"]');

-- --------------------------------------------------------

--
-- Table structure for table `notices`
--

CREATE TABLE `notices` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `end_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ol_result_accountant`
--

CREATE TABLE `ol_result_accountant` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `subject_name` varchar(100) DEFAULT NULL,
  `result` varchar(5) DEFAULT NULL,
  `index_number` varchar(20) DEFAULT NULL,
  `year` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ol_result_staff`
--

CREATE TABLE `ol_result_staff` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `subject_name` varchar(100) DEFAULT NULL,
  `result` enum('A','B','C','S','W') NOT NULL,
  `index_number` varchar(50) NOT NULL,
  `year` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ol_result_staff`
--

INSERT INTO `ol_result_staff` (`id`, `username`, `subject_name`, `result`, `index_number`, `year`) VALUES
(1, 'staff', 'asd', 'A', '21212121', 2001),
(2, 'staff', 'asd', 'A', '21212121', 2001),
(3, 'staff', 'asd', 'A', '21212121', 2001),
(4, 'staff', 'asd', 'A', '21212121', 2001),
(5, 'staff', 'asd', 'B', '21212121', 2001),
(6, 'staff', 'asd', 'A', '21212121', 2001),
(7, 'staff', 'asd', 'B', '21212121', 2001),
(8, 'staff', 'asd', 'A', '21212121', 2001),
(9, 'staff', 'asd', 'A', '21212121', 2001);

-- --------------------------------------------------------

--
-- Table structure for table `ol_result_teacher`
--

CREATE TABLE `ol_result_teacher` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `subject_name` varchar(100) DEFAULT NULL,
  `result` enum('A','B','C','S','W') NOT NULL,
  `index_number` varchar(50) NOT NULL,
  `year` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ol_result_teacher`
--

INSERT INTO `ol_result_teacher` (`id`, `username`, `subject_name`, `result`, `index_number`, `year`) VALUES
(10, 'teacher', 'asd', 'A', '21212121', 2003),
(11, 'teacher', 'asd', 'A', '21212121', 2003),
(12, 'teacher', 'asd', 'A', '21212121', 2003),
(13, 'teacher', 'asd', 'A', '21212121', 2003),
(14, 'teacher', 'asd', 'A', '21212121', 2003),
(15, 'teacher', 'asd', 'A', '21212121', 2003),
(16, 'teacher', 'asd', 'A', '21212121', 2003),
(17, 'teacher', 'asd', 'A', '21212121', 2003),
(18, 'teacher', 'asd', 'A', '21212121', 2003),
(19, 'subteacher', 'asd', 'A', '21212121', 2002),
(20, 'subteacher', 'asd', 'A', '21212121', 2002),
(21, 'subteacher', 'asd', 'A', '21212121', 2002),
(22, 'subteacher', 'asd', 'B', '21212121', 2002),
(23, 'subteacher', 'asd', 'A', '21212121', 2002),
(24, 'subteacher', 'asd', 'A', '21212121', 2002),
(25, 'subteacher', 'asd', 'A', '21212121', 2002),
(26, 'subteacher', 'asd', 'A', '21212121', 2002),
(27, 'subteacher', 'asd', 'A', '21212121', 2002);

-- --------------------------------------------------------

--
-- Table structure for table `other_payments`
--

CREATE TABLE `other_payments` (
  `id` int(11) NOT NULL,
  `payment_name` varchar(255) NOT NULL,
  `payment_amount` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `grade` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `previous_info`
--

CREATE TABLE `previous_info` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `previous_role` varchar(255) DEFAULT NULL,
  `previous_company` varchar(255) DEFAULT NULL,
  `years_experience` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `previous_info`
--

INSERT INTO `previous_info` (`id`, `username`, `previous_role`, `previous_company`, `years_experience`) VALUES
(2, 'teacher', 'Teacher', 'School', 3),
(3, 'subteacher', 'Teacher', 'School', 2),
(4, 'staff', 'Clerk', 'School', 2),
(5, 'staff', 'Teacher', 'School', 9);

-- --------------------------------------------------------

--
-- Table structure for table `previous_info_accountant`
--

CREATE TABLE `previous_info_accountant` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `previous_role` varchar(255) DEFAULT NULL,
  `previous_company` varchar(255) DEFAULT NULL,
  `years_experience` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pre_addmission_details`
--

CREATE TABLE `pre_addmission_details` (
  `id` int(11) NOT NULL,
  `parent_name` varchar(255) NOT NULL,
  `relationship` varchar(100) NOT NULL,
  `student_name` varchar(255) NOT NULL,
  `dob` date NOT NULL,
  `address` text NOT NULL,
  `contact_number` varchar(15) NOT NULL,
  `requested_class` varchar(50) NOT NULL,
  `submission_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `email` varchar(255) NOT NULL,
  `nic_number` varchar(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ranks`
--

CREATE TABLE `ranks` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `grade_id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `term` varchar(50) NOT NULL,
  `rank` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `siblings`
--

CREATE TABLE `siblings` (
  `id` int(11) NOT NULL,
  `student_admission_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `gender` varchar(50) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `school` varchar(255) DEFAULT NULL,
  `grade` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `siblings`
--

INSERT INTO `siblings` (`id`, `student_admission_id`, `name`, `gender`, `dob`, `school`, `grade`) VALUES
(1, 1, 'ujkj', 'male', '2025-08-30', 'jlljlj', 'khg');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `date_of_birth` date NOT NULL,
  `postal_address` text NOT NULL,
  `ethnicity` varchar(255) DEFAULT NULL,
  `nic_number` varchar(50) NOT NULL,
  `marital_status` enum('Single','Married') NOT NULL,
  `whatsapp_number` varchar(15) DEFAULT NULL,
  `residence_number` varchar(15) DEFAULT NULL,
  `first_language` varchar(50) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `username`, `full_name`, `gender`, `date_of_birth`, `postal_address`, `ethnicity`, `nic_number`, `marital_status`, `whatsapp_number`, `residence_number`, `first_language`, `profile_image`, `position`) VALUES
(1, 'staff', 'Staff', 'Male', '2025-08-31', '123 Sri Lanka', 'Sri Lankan', '200322500850', 'Single', '0777111666', '0777525252', 'Sinhal', '', 'Accountant');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `grade_id` int(11) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `name`, `username`, `password`, `grade_id`, `active`) VALUES
(1, 'Test', 'test', '$2y$10$2VGFQgvn6r71oN59tbwg.ed7o0jZhAyqwaxt4jbAbaogpteGise3y', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `student_admissions`
--

CREATE TABLE `student_admissions` (
  `id` int(11) NOT NULL,
  `student_name` varchar(255) NOT NULL,
  `student_address` text NOT NULL,
  `student_dob` date NOT NULL,
  `student_gender` enum('Male','Female','Other') NOT NULL,
  `student_nationality` varchar(100) NOT NULL,
  `student_religion` varchar(100) NOT NULL,
  `student_mother_tongue` varchar(100) NOT NULL,
  `student_image` varchar(255) DEFAULT NULL,
  `student_phone` varchar(20) DEFAULT NULL,
  `assigning_grade` varchar(50) NOT NULL,
  `school_attended` varchar(255) DEFAULT NULL,
  `school_address` text DEFAULT NULL,
  `school_medium` varchar(100) DEFAULT NULL,
  `second_language` varchar(100) DEFAULT NULL,
  `grade_passed` varchar(50) DEFAULT NULL,
  `last_attended` date DEFAULT NULL,
  `duration_of_stay` varchar(50) DEFAULT NULL,
  `reason_for_leaving` text DEFAULT NULL,
  `special_attention` text DEFAULT NULL,
  `father_name` varchar(255) DEFAULT NULL,
  `father_id` varchar(100) DEFAULT NULL,
  `father_dob` date DEFAULT NULL,
  `father_occupation` varchar(255) DEFAULT NULL,
  `father_school` varchar(255) DEFAULT NULL,
  `father_education` varchar(255) DEFAULT NULL,
  `father_mobile` varchar(20) DEFAULT NULL,
  `father_residence` text DEFAULT NULL,
  `father_email` varchar(255) DEFAULT NULL,
  `mother_name` varchar(255) DEFAULT NULL,
  `mother_id` varchar(100) DEFAULT NULL,
  `mother_dob` date DEFAULT NULL,
  `mother_occupation` varchar(255) DEFAULT NULL,
  `mother_school` varchar(255) DEFAULT NULL,
  `mother_education` varchar(255) DEFAULT NULL,
  `mother_mobile` varchar(20) DEFAULT NULL,
  `mother_residence` text DEFAULT NULL,
  `mother_email` varchar(255) DEFAULT NULL,
  `guardian_name` varchar(255) DEFAULT NULL,
  `guardian_id` varchar(100) DEFAULT NULL,
  `guardian_dob` date DEFAULT NULL,
  `guardian_occupation` varchar(255) DEFAULT NULL,
  `guardian_school` varchar(255) DEFAULT NULL,
  `guardian_education` varchar(255) DEFAULT NULL,
  `guardian_mobile` varchar(20) DEFAULT NULL,
  `guardian_residence` text DEFAULT NULL,
  `guardian_email` varchar(255) DEFAULT NULL,
  `guardian_relationship` varchar(100) DEFAULT NULL,
  `guardian_reason` text DEFAULT NULL,
  `siblings` text DEFAULT NULL,
  `parents_together` enum('Yes','No') NOT NULL,
  `parents_reason` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `emergency_name` varchar(255) NOT NULL,
  `emergency_relationship` varchar(100) NOT NULL,
  `emergency_mobile` varchar(20) NOT NULL,
  `emergency_residence` text DEFAULT NULL,
  `emergency_office` text DEFAULT NULL,
  `emergency_fax` varchar(20) DEFAULT NULL,
  `signature_image` blob DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_admissions`
--

INSERT INTO `student_admissions` (`id`, `student_name`, `student_address`, `student_dob`, `student_gender`, `student_nationality`, `student_religion`, `student_mother_tongue`, `student_image`, `student_phone`, `assigning_grade`, `school_attended`, `school_address`, `school_medium`, `second_language`, `grade_passed`, `last_attended`, `duration_of_stay`, `reason_for_leaving`, `special_attention`, `father_name`, `father_id`, `father_dob`, `father_occupation`, `father_school`, `father_education`, `father_mobile`, `father_residence`, `father_email`, `mother_name`, `mother_id`, `mother_dob`, `mother_occupation`, `mother_school`, `mother_education`, `mother_mobile`, `mother_residence`, `mother_email`, `guardian_name`, `guardian_id`, `guardian_dob`, `guardian_occupation`, `guardian_school`, `guardian_education`, `guardian_mobile`, `guardian_residence`, `guardian_email`, `guardian_relationship`, `guardian_reason`, `siblings`, `parents_together`, `parents_reason`, `remarks`, `emergency_name`, `emergency_relationship`, `emergency_mobile`, `emergency_residence`, `emergency_office`, `emergency_fax`, `signature_image`, `status`, `created_at`) VALUES
(1, 'Test', '123/ test', '2003-08-12', 'Male', 'Sri Lanka', 'Muslim', 'Tamil', '', '0722211111', '1', '', '', '', '', '', '0000-00-00', '', '', '', 'Test', '222200022202', '1984-11-14', 'Busniess', 'School', 'Passed', '0222111232', 'World', 'test@gmail.com', 'Test', '299393939339', '1987-07-09', 'Wife', 'No', 'No', '02202928282', 'world', 'testmom@gmail.com', 'gggg', '2222222222222222', '2025-08-30', 'no', 'no', 'ljnlnlj', '2222244444', 'asd', 'ggg@gmail.com', 'Uncle', 'ljkgyiti', NULL, 'Yes', '', 'sadasdasd', 'Dad', 'Dad', '0222838383', 'World', '23232323', '3634345345345', 0x75706c6f6164732f456e68616e6365207468652075706c6f616465642e706e67, 'approved', '2025-08-30 07:00:09');

-- --------------------------------------------------------

--
-- Table structure for table `student_block_reasons`
--

CREATE TABLE `student_block_reasons` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `block_reason` text NOT NULL,
  `blocked_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_marks`
--

CREATE TABLE `student_marks` (
  `exam_id` int(11) NOT NULL,
  `student_username` varchar(50) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `marks` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_messages`
--

CREATE TABLE `student_messages` (
  `id` int(11) NOT NULL,
  `sender_username` varchar(50) NOT NULL,
  `receiver_username` varchar(50) DEFAULT NULL,
  `content` text NOT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `grade_id` int(11) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_payments`
--

CREATE TABLE `student_payments` (
  `id` int(11) NOT NULL,
  `student_username` varchar(255) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `status` varchar(10) DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `grade_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_send_messages`
--

CREATE TABLE `student_send_messages` (
  `id` int(11) NOT NULL,
  `sender_username` varchar(255) NOT NULL,
  `receiver_username` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `is_broadcast` tinyint(1) DEFAULT 0,
  `grade_id` int(11) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `subject_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `subject_name`) VALUES
(1, 'Sinhala'),
(2, 'English'),
(3, 'Islam'),
(4, 'Science '),
(5, 'Arts'),
(6, 'History');

-- --------------------------------------------------------

--
-- Table structure for table `subject_feedbacks`
--

CREATE TABLE `subject_feedbacks` (
  `id` int(11) NOT NULL,
  `teacher_username` varchar(50) NOT NULL,
  `grade_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `feedback` text NOT NULL,
  `feedback_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teacher`
--

CREATE TABLE `teacher` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `date_of_birth` date NOT NULL,
  `postal_address` text NOT NULL,
  `ethnicity` varchar(255) DEFAULT NULL,
  `nic_number` varchar(50) NOT NULL,
  `marital_status` enum('Single','Married') NOT NULL,
  `whatsapp_number` varchar(15) DEFAULT NULL,
  `residence_number` varchar(15) DEFAULT NULL,
  `first_language` varchar(50) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `grade_id` int(11) DEFAULT NULL,
  `teaching_classes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`teaching_classes`)),
  `rank` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher`
--

INSERT INTO `teacher` (`id`, `username`, `full_name`, `gender`, `date_of_birth`, `postal_address`, `ethnicity`, `nic_number`, `marital_status`, `whatsapp_number`, `residence_number`, `first_language`, `profile_image`, `subject_id`, `grade_id`, `teaching_classes`, `rank`) VALUES
(2, 'teacher', 'teacher', 'Male', '2025-08-31', '123 Sri Lanka', 'sri lankan', '222222222222222222', 'Single', '07771116666', '07775252232', 'English', 'Uploads/Enhance the uploaded.png', 4, 1, '[\"1\"]', '1st');

-- --------------------------------------------------------

--
-- Table structure for table `tests`
--

CREATE TABLE `tests` (
  `id` int(11) NOT NULL,
  `type` enum('Monthly Test','Unit Test') NOT NULL,
  `grade_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `teacher_username` varchar(50) DEFAULT NULL,
  `noclass_teacher_username` varchar(50) DEFAULT NULL,
  `staff_username` varchar(50) DEFAULT NULL,
  `test_date` date NOT NULL,
  `publish_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `test_marks`
--

CREATE TABLE `test_marks` (
  `id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  `student_username` varchar(50) NOT NULL,
  `marks_obtained` float NOT NULL,
  `rank` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `timetables`
--

CREATE TABLE `timetables` (
  `id` int(11) NOT NULL,
  `grade_id` int(11) NOT NULL,
  `day` enum('Monday','Tuesday','Wednesday','Thursday','Friday') NOT NULL,
  `period` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `username` varchar(50) NOT NULL,
  `role` enum('Teacher','NoClass_Teacher','Staff') NOT NULL,
  `password` varchar(255) NOT NULL,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`username`, `role`, `password`, `active`) VALUES
('staff', 'Staff', '$2y$10$U4Fb/zTM4ak2x7.I9CPTAu3OkiH8T4jg4.GJ9t1qtrICxtBXNsFmO', 1),
('subteacher', 'NoClass_Teacher', '$2y$10$mDxZqxS4kynD3FUEK6gECOFd0kw49gYAcR/X1XpxhX71DvkpHHhJm', 1),
('teacher', 'Teacher', '$2y$10$0mBQnjSRYNAwjoAzebQ1OODXzBrSXRMNVcay2WGmbBVzmaYvBKvQy', 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_messages`
--

CREATE TABLE `user_messages` (
  `id` int(11) NOT NULL,
  `sender_username` varchar(50) NOT NULL,
  `receiver_username` varchar(50) DEFAULT NULL,
  `content` text NOT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `target_role` enum('Teacher','NoClass_Teacher','Staff') DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accountant`
--
ALTER TABLE `accountant`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `nic_number` (`nic_number`);

--
-- Indexes for table `accountant_block_reasons`
--
ALTER TABLE `accountant_block_reasons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `al_result_accountant`
--
ALTER TABLE `al_result_accountant`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `al_result_staff`
--
ALTER TABLE `al_result_staff`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `al_result_teacher`
--
ALTER TABLE `al_result_teacher`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `assign_payment`
--
ALTER TABLE `assign_payment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`,`attendance_date`),
  ADD KEY `teacher_user_id` (`teacher_user_id`);

--
-- Indexes for table `block_reasons`
--
ALTER TABLE `block_reasons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chapters`
--
ALTER TABLE `chapters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `grade_id` (`grade_id`);

--
-- Indexes for table `educational_details`
--
ALTER TABLE `educational_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `educational_details_accountant`
--
ALTER TABLE `educational_details_accountant`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `grade_id` (`grade_id`);

--
-- Indexes for table `exam_subjects`
--
ALTER TABLE `exam_subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exam_id` (`exam_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_username` (`teacher_username`),
  ADD KEY `grade_id` (`grade_id`);

--
-- Indexes for table `fee_registration`
--
ALTER TABLE `fee_registration`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `receipt_no` (`receipt_no`);

--
-- Indexes for table `gallery_images`
--
ALTER TABLE `gallery_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `grade_subject`
--
ALTER TABLE `grade_subject`
  ADD PRIMARY KEY (`grade_id`,`subject_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_username` (`sender_username`),
  ADD KEY `receiver_username` (`receiver_username`),
  ADD KEY `grade_id` (`grade_id`),
  ADD KEY `fk_user_sender` (`user_sender`);

--
-- Indexes for table `noclass_teacher`
--
ALTER TABLE `noclass_teacher`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nic_number` (`nic_number`),
  ADD KEY `username` (`username`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `notices`
--
ALTER TABLE `notices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ol_result_accountant`
--
ALTER TABLE `ol_result_accountant`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `ol_result_staff`
--
ALTER TABLE `ol_result_staff`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `ol_result_teacher`
--
ALTER TABLE `ol_result_teacher`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `other_payments`
--
ALTER TABLE `other_payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `previous_info`
--
ALTER TABLE `previous_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `previous_info_accountant`
--
ALTER TABLE `previous_info_accountant`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `pre_addmission_details`
--
ALTER TABLE `pre_addmission_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nic_number` (`nic_number`);

--
-- Indexes for table `ranks`
--
ALTER TABLE `ranks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`),
  ADD KEY `grade_id` (`grade_id`),
  ADD KEY `exam_id` (`exam_id`);

--
-- Indexes for table `siblings`
--
ALTER TABLE `siblings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_admission_id` (`student_admission_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nic_number` (`nic_number`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `grade_id` (`grade_id`);

--
-- Indexes for table `student_admissions`
--
ALTER TABLE `student_admissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_block_reasons`
--
ALTER TABLE `student_block_reasons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `student_marks`
--
ALTER TABLE `student_marks`
  ADD PRIMARY KEY (`exam_id`,`student_username`,`subject_id`),
  ADD KEY `student_username` (`student_username`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `student_messages`
--
ALTER TABLE `student_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_username` (`sender_username`),
  ADD KEY `receiver_username` (`receiver_username`),
  ADD KEY `grade_id` (`grade_id`);

--
-- Indexes for table `student_payments`
--
ALTER TABLE `student_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_id` (`payment_id`),
  ADD KEY `student_username` (`student_username`);

--
-- Indexes for table `student_send_messages`
--
ALTER TABLE `student_send_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `receiver_username` (`receiver_username`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subject_feedbacks`
--
ALTER TABLE `subject_feedbacks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `grade_id` (`grade_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `teacher`
--
ALTER TABLE `teacher`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nic_number` (`nic_number`),
  ADD KEY `username` (`username`),
  ADD KEY `grade_id` (`grade_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `tests`
--
ALTER TABLE `tests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `grade_id` (`grade_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `teacher_username` (`teacher_username`),
  ADD KEY `noclass_teacher_username` (`noclass_teacher_username`),
  ADD KEY `staff_username` (`staff_username`);

--
-- Indexes for table `test_marks`
--
ALTER TABLE `test_marks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `test_id` (`test_id`),
  ADD KEY `student_username` (`student_username`);

--
-- Indexes for table `timetables`
--
ALTER TABLE `timetables`
  ADD PRIMARY KEY (`id`),
  ADD KEY `grade_id` (`grade_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `user_messages`
--
ALTER TABLE `user_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_username` (`sender_username`),
  ADD KEY `receiver_username` (`receiver_username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accountant`
--
ALTER TABLE `accountant`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `accountant_block_reasons`
--
ALTER TABLE `accountant_block_reasons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `al_result_accountant`
--
ALTER TABLE `al_result_accountant`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `al_result_staff`
--
ALTER TABLE `al_result_staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `al_result_teacher`
--
ALTER TABLE `al_result_teacher`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assign_payment`
--
ALTER TABLE `assign_payment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `block_reasons`
--
ALTER TABLE `block_reasons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `chapters`
--
ALTER TABLE `chapters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `educational_details`
--
ALTER TABLE `educational_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `educational_details_accountant`
--
ALTER TABLE `educational_details_accountant`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exams`
--
ALTER TABLE `exams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_subjects`
--
ALTER TABLE `exam_subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedbacks`
--
ALTER TABLE `feedbacks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fee_registration`
--
ALTER TABLE `fee_registration`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gallery_images`
--
ALTER TABLE `gallery_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `noclass_teacher`
--
ALTER TABLE `noclass_teacher`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notices`
--
ALTER TABLE `notices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ol_result_accountant`
--
ALTER TABLE `ol_result_accountant`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ol_result_staff`
--
ALTER TABLE `ol_result_staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `ol_result_teacher`
--
ALTER TABLE `ol_result_teacher`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `other_payments`
--
ALTER TABLE `other_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `previous_info`
--
ALTER TABLE `previous_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `previous_info_accountant`
--
ALTER TABLE `previous_info_accountant`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pre_addmission_details`
--
ALTER TABLE `pre_addmission_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ranks`
--
ALTER TABLE `ranks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `siblings`
--
ALTER TABLE `siblings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `student_admissions`
--
ALTER TABLE `student_admissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `student_block_reasons`
--
ALTER TABLE `student_block_reasons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `student_messages`
--
ALTER TABLE `student_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `student_payments`
--
ALTER TABLE `student_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_send_messages`
--
ALTER TABLE `student_send_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `subject_feedbacks`
--
ALTER TABLE `subject_feedbacks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teacher`
--
ALTER TABLE `teacher`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tests`
--
ALTER TABLE `tests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `test_marks`
--
ALTER TABLE `test_marks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `timetables`
--
ALTER TABLE `timetables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_messages`
--
ALTER TABLE `user_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accountant_block_reasons`
--
ALTER TABLE `accountant_block_reasons`
  ADD CONSTRAINT `accountant_block_reasons_ibfk_1` FOREIGN KEY (`username`) REFERENCES `accountant` (`username`) ON DELETE CASCADE;

--
-- Constraints for table `al_result_accountant`
--
ALTER TABLE `al_result_accountant`
  ADD CONSTRAINT `al_result_accountant_ibfk_1` FOREIGN KEY (`username`) REFERENCES `accountant` (`username`);

--
-- Constraints for table `al_result_staff`
--
ALTER TABLE `al_result_staff`
  ADD CONSTRAINT `al_result_staff_ibfk_1` FOREIGN KEY (`username`) REFERENCES `user` (`username`) ON DELETE CASCADE;

--
-- Constraints for table `al_result_teacher`
--
ALTER TABLE `al_result_teacher`
  ADD CONSTRAINT `al_result_teacher_ibfk_1` FOREIGN KEY (`username`) REFERENCES `user` (`username`) ON DELETE CASCADE;

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`username`) REFERENCES `user` (`username`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignments_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `grades` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`teacher_user_id`) REFERENCES `teacher` (`username`) ON DELETE CASCADE;

--
-- Constraints for table `block_reasons`
--
ALTER TABLE `block_reasons`
  ADD CONSTRAINT `block_reasons_ibfk_1` FOREIGN KEY (`username`) REFERENCES `user` (`username`) ON DELETE CASCADE;

--
-- Constraints for table `chapters`
--
ALTER TABLE `chapters`
  ADD CONSTRAINT `chapters_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chapters_ibfk_2` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `educational_details`
--
ALTER TABLE `educational_details`
  ADD CONSTRAINT `educational_details_ibfk_1` FOREIGN KEY (`username`) REFERENCES `user` (`username`) ON DELETE CASCADE;

--
-- Constraints for table `educational_details_accountant`
--
ALTER TABLE `educational_details_accountant`
  ADD CONSTRAINT `educational_details_accountant_ibfk_1` FOREIGN KEY (`username`) REFERENCES `accountant` (`username`) ON DELETE CASCADE;

--
-- Constraints for table `exams`
--
ALTER TABLE `exams`
  ADD CONSTRAINT `exams_ibfk_1` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_subjects`
--
ALTER TABLE `exam_subjects`
  ADD CONSTRAINT `exam_subjects_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_subjects_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD CONSTRAINT `feedbacks_ibfk_1` FOREIGN KEY (`teacher_username`) REFERENCES `teacher` (`username`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedbacks_ibfk_2` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `gallery_images`
--
ALTER TABLE `gallery_images`
  ADD CONSTRAINT `gallery_images_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `grade_subject`
--
ALTER TABLE `grade_subject`
  ADD CONSTRAINT `grade_subject_ibfk_1` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grade_subject_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_user_sender` FOREIGN KEY (`user_sender`) REFERENCES `user` (`username`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_username`) REFERENCES `admin` (`username`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_username`) REFERENCES `user` (`username`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `noclass_teacher`
--
ALTER TABLE `noclass_teacher`
  ADD CONSTRAINT `noclass_teacher_ibfk_1` FOREIGN KEY (`username`) REFERENCES `user` (`username`) ON DELETE CASCADE,
  ADD CONSTRAINT `noclass_teacher_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ol_result_accountant`
--
ALTER TABLE `ol_result_accountant`
  ADD CONSTRAINT `ol_result_accountant_ibfk_1` FOREIGN KEY (`username`) REFERENCES `accountant` (`username`);

--
-- Constraints for table `ol_result_staff`
--
ALTER TABLE `ol_result_staff`
  ADD CONSTRAINT `ol_result_staff_ibfk_1` FOREIGN KEY (`username`) REFERENCES `user` (`username`) ON DELETE CASCADE;

--
-- Constraints for table `ol_result_teacher`
--
ALTER TABLE `ol_result_teacher`
  ADD CONSTRAINT `ol_result_teacher_ibfk_1` FOREIGN KEY (`username`) REFERENCES `user` (`username`) ON DELETE CASCADE;

--
-- Constraints for table `previous_info`
--
ALTER TABLE `previous_info`
  ADD CONSTRAINT `previous_info_ibfk_1` FOREIGN KEY (`username`) REFERENCES `user` (`username`) ON DELETE CASCADE;

--
-- Constraints for table `previous_info_accountant`
--
ALTER TABLE `previous_info_accountant`
  ADD CONSTRAINT `previous_info_accountant_ibfk_1` FOREIGN KEY (`username`) REFERENCES `accountant` (`username`) ON DELETE CASCADE;

--
-- Constraints for table `ranks`
--
ALTER TABLE `ranks`
  ADD CONSTRAINT `ranks_ibfk_1` FOREIGN KEY (`username`) REFERENCES `students` (`username`) ON DELETE CASCADE,
  ADD CONSTRAINT `ranks_ibfk_2` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ranks_ibfk_3` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `siblings`
--
ALTER TABLE `siblings`
  ADD CONSTRAINT `siblings_ibfk_1` FOREIGN KEY (`student_admission_id`) REFERENCES `student_admissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`username`) REFERENCES `user` (`username`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`id`) REFERENCES `student_admissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `students_ibfk_2` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_block_reasons`
--
ALTER TABLE `student_block_reasons`
  ADD CONSTRAINT `student_block_reasons_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_marks`
--
ALTER TABLE `student_marks`
  ADD CONSTRAINT `student_marks_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_marks_ibfk_2` FOREIGN KEY (`student_username`) REFERENCES `students` (`username`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_marks_ibfk_3` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_messages`
--
ALTER TABLE `student_messages`
  ADD CONSTRAINT `student_messages_ibfk_1` FOREIGN KEY (`sender_username`) REFERENCES `students` (`username`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_messages_ibfk_2` FOREIGN KEY (`receiver_username`) REFERENCES `admin` (`username`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_messages_ibfk_3` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_payments`
--
ALTER TABLE `student_payments`
  ADD CONSTRAINT `student_payments_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `other_payments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_payments_ibfk_2` FOREIGN KEY (`student_username`) REFERENCES `students` (`username`) ON DELETE CASCADE;

--
-- Constraints for table `student_send_messages`
--
ALTER TABLE `student_send_messages`
  ADD CONSTRAINT `student_send_messages_ibfk_1` FOREIGN KEY (`receiver_username`) REFERENCES `students` (`username`) ON DELETE CASCADE;

--
-- Constraints for table `subject_feedbacks`
--
ALTER TABLE `subject_feedbacks`
  ADD CONSTRAINT `subject_feedbacks_ibfk_1` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subject_feedbacks_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teacher`
--
ALTER TABLE `teacher`
  ADD CONSTRAINT `teacher_ibfk_1` FOREIGN KEY (`username`) REFERENCES `user` (`username`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_ibfk_2` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_ibfk_3` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tests`
--
ALTER TABLE `tests`
  ADD CONSTRAINT `tests_ibfk_1` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tests_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tests_ibfk_3` FOREIGN KEY (`teacher_username`) REFERENCES `teacher` (`username`) ON DELETE CASCADE,
  ADD CONSTRAINT `tests_ibfk_4` FOREIGN KEY (`noclass_teacher_username`) REFERENCES `noclass_teacher` (`username`) ON DELETE CASCADE,
  ADD CONSTRAINT `tests_ibfk_5` FOREIGN KEY (`staff_username`) REFERENCES `staff` (`username`) ON DELETE CASCADE;

--
-- Constraints for table `test_marks`
--
ALTER TABLE `test_marks`
  ADD CONSTRAINT `test_marks_ibfk_1` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `test_marks_ibfk_2` FOREIGN KEY (`student_username`) REFERENCES `students` (`username`) ON DELETE CASCADE;

--
-- Constraints for table `timetables`
--
ALTER TABLE `timetables`
  ADD CONSTRAINT `timetables_ibfk_1` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_messages`
--
ALTER TABLE `user_messages`
  ADD CONSTRAINT `user_messages_ibfk_1` FOREIGN KEY (`sender_username`) REFERENCES `user` (`username`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_messages_ibfk_2` FOREIGN KEY (`receiver_username`) REFERENCES `admin` (`username`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
