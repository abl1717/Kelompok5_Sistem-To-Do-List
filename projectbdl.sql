-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 07, 2025 at 06:21 AM
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
-- Database: `projectbdl`
--

-- --------------------------------------------------------

--
-- Stand-in structure for view `completed_todos`
-- (See below for the actual view)
--
CREATE TABLE `completed_todos` (
`id` int(11)
,`title` varchar(100)
,`username` varchar(50)
,`status` enum('pending','completed')
);

-- --------------------------------------------------------

--
-- Table structure for table `todos`
--

CREATE TABLE `todos` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `priority` int(11) DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `status` enum('pending','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `todos`
--

INSERT INTO `todos` (`id`, `user_id`, `title`, `description`, `priority`, `deadline`, `status`, `created_at`) VALUES
(9, 3, 'Tes edit', 'ini tes', 4, '2025-07-07', 'completed', '2025-07-05 20:48:04'),
(10, 3, 'Tes lagi', 'sbajkbgkds', 5, '2025-07-06', 'pending', '2025-07-05 20:48:46');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`) VALUES
(1, 'abl', 'abil24ti@mahasiswa.pcr.ac.id', '$2y$10$VTzc82PapIxupigucMBX2OhqQudYCqr3ojBwVAA1E5YbuaHJ/qdF2'),
(2, 'aar', 'abil24ti@mahasiswa.pcr.ac.id', '$2y$10$mVaOvi89w36TnX89mBZbQeTp/Wu0kljJIHvHnMTJkOmFxlBeUiHW2'),
(3, 'jny', 'abil24ti@mahasiswa.pcr.ac.id', '$2y$10$gSX/tHo0qyaN8F/QidhcpuZ1IGb4ZvuAHHFA.atlnu1bTuR7HeFwe');

-- --------------------------------------------------------

--
-- Structure for view `completed_todos`
--
DROP TABLE IF EXISTS `completed_todos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `completed_todos`  AS SELECT `t`.`id` AS `id`, `t`.`title` AS `title`, `u`.`username` AS `username`, `t`.`status` AS `status` FROM (`todos` `t` join `users` `u` on(`t`.`user_id` = `u`.`id`)) WHERE `t`.`status` = 'completed' ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `todos`
--
ALTER TABLE `todos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `todos`
--
ALTER TABLE `todos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `todos`
--
ALTER TABLE `todos`
  ADD CONSTRAINT `todos_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;



-- Kalo udah dibuat tabelnya baru buat ini dik!

-- VIEW: Melihat todo yang sudah selesai
CREATE VIEW completed_todos AS
SELECT t.id, t.title, u.username, t.status
FROM todos t JOIN users u ON t.user_id = u.id
WHERE t.status = 'completed';


-- PROCEDURE: Menandai tugas selesai
DELIMITER //
CREATE PROCEDURE mark_done(IN todoId INT)
BEGIN
    UPDATE todos SET status = 'completed' WHERE id = todoId;
END;
//
DELIMITER ;


-- FUNCTION: Hitung total todo milik user
DELIMITER //
CREATE FUNCTION total_todo(userId INT)
RETURNS INT
DETERMINISTIC
BEGIN
    DECLARE total INT;
    SELECT COUNT(*) INTO total FROM todos WHERE user_id = userId;
    RETURN total;
END;
//
DELIMITER ;

-- FUNCTION: Menentukan rata rata prioritas todo milik user
DELIMITER //

-- Fungsi ini menghitung rata-rata tugas yang diselesaikan per hari oleh user tertentu.
CREATE FUNCTION avg_complete(userId INT)
RETURNS DECIMAL(10, 2) -- Mengembalikan nilai desimal dengan 2 angka di belakang koma
DETERMINISTIC
BEGIN
    DECLARE total_completed_tasks INT;
    DECLARE first_completed_date DATE;
    DECLARE last_completed_date DATE;
    DECLARE number_of_distinct_days INT;
    DECLARE avg DECIMAL(10, 2);

    -- 1. Hitung total tugas yang diselesaikan oleh user tertentu
    SELECT COUNT(*)
    INTO total_completed_tasks
    FROM todos
    WHERE status = 'completed'
      AND user_id = userId;

    -- 2. Temukan tanggal pertama dan terakhir user ini menyelesaikan tugas
    SELECT MIN(DATE(created_at)), MAX(DATE(created_at))
    INTO first_completed_date, last_completed_date
    FROM todos
    WHERE status = 'completed'
      AND user_id = userId;

    -- 3. Hitung jumlah hari unik di mana user ini menyelesaikan tugas
    -- Jika tidak ada tugas yang diselesaikan, number_of_distinct_days akan 0
    IF total_completed_tasks > 0 THEN
        -- Menghitung jumlah hari unik antara tanggal pertama dan terakhir
        -- Ini lebih akurat daripada DATEDIFF karena DATEDIFF hanya menghitung selisih hari
        -- dan tidak mempertimbangkan apakah ada tugas yang diselesaikan setiap hari.
        -- Untuk mendapatkan jumlah hari unik, kita bisa menggunakan subquery.
        SELECT COUNT(DISTINCT DATE(created_at))
        INTO number_of_distinct_days
        FROM todos
        WHERE status = 'completed'
          AND user_id = userId;
    ELSE
        SET number_of_distinct_days = 0;
    END IF;

    -- 4. Hitung rata-rata
    IF number_of_distinct_days > 0 THEN
        SET avg = total_completed_tasks / number_of_distinct_days;
    ELSE
        SET avg = 0.00; -- Jika tidak ada tugas yang diselesaikan atau tidak ada hari aktivitas
    END IF;

    RETURN avg;
END //

DELIMITER ;

-- Cara memanggil fungsi ini untuk user_id = 1:
-- SELECT get_user_average_completed_tasks_per_day(1);


-- Ini kayaknya gausah ditambahin ke database dik

-- Total task
SELECT COUNT(*) AS total_task FROM todos;

-- Deadline terdekat
SELECT MIN(deadline) AS next_deadline FROM todos;

-- Prioritas tertinggi
SELECT MAX(priority) AS max_priority FROM todos;

-- Total prioritas
SELECT AVG(priority) AS max_priority FROM todos;


