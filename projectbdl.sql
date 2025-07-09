-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 09 Jul 2025 pada 23.37
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

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

DELIMITER $$
--
-- Prosedur
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `mark_done` (IN `todoId` INT)   BEGIN
    UPDATE todos SET status = 'completed' WHERE id = todoId;
END$$

--
-- Fungsi
--
CREATE DEFINER=`root`@`localhost` FUNCTION `avg_complete` (`userId` INT) RETURNS DECIMAL(10,2) DETERMINISTIC BEGIN
    DECLARE total_completed_tasks INT;
    DECLARE first_completed_date DATE;
    DECLARE last_completed_date DATE;
    DECLARE number_of_distinct_days INT;
    DECLARE avg DECIMAL(10, 2);
    SELECT COUNT(*)
    INTO total_completed_tasks
    FROM todos
    WHERE status = 'completed'
      AND user_id = userId;

    SELECT MIN(DATE(created_at)), MAX(DATE(created_at))
    INTO first_completed_date, last_completed_date
    FROM todos
    WHERE status = 'completed'
      AND user_id = userId;
    IF total_completed_tasks > 0 THEN
        SELECT COUNT(DISTINCT DATE(created_at))
        INTO number_of_distinct_days
        FROM todos
        WHERE status = 'completed'
          AND user_id = userId;
    ELSE
        SET number_of_distinct_days = 0;
    END IF;

    IF number_of_distinct_days > 0 THEN
        SET avg = total_completed_tasks / number_of_distinct_days;
    ELSE
        SET avg = 0.00; 
    END IF;

    RETURN avg;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `total_todo` (`userId` INT) RETURNS INT(11) DETERMINISTIC BEGIN
    DECLARE total INT;
    SELECT COUNT(*) INTO total FROM todos WHERE user_id = userId;
    RETURN total;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `completed_todos`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `completed_todos` (
`id` int(11)
,`title` varchar(100)
,`username` varchar(50)
,`status` enum('pending','completed')
);

-- --------------------------------------------------------

--
-- Struktur dari tabel `todos`
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
-- Dumping data untuk tabel `todos`
--

INSERT INTO `todos` (`id`, `user_id`, `title`, `description`, `priority`, `deadline`, `status`, `created_at`) VALUES
(9, 3, 'Tes edit', 'ini tes', 4, '2025-07-07', 'completed', '2025-07-05 20:48:04'),
(10, 3, 'Tes lagi', 'sbajkbgkds', 5, '2025-07-06', 'pending', '2025-07-05 20:48:46'),
(11, 4, 'Makan', 'Makan nasi biar kenyang dan lauk pauk bergizi dari mamah', 5, '0000-00-00', 'completed', '2025-07-07 07:26:21'),
(12, 4, '1', '1', 1, '0000-00-00', 'completed', '2025-07-07 07:27:36'),
(13, 4, '2', '2', 2, '0000-00-00', 'completed', '2025-07-07 07:32:36'),
(14, 8, 'asd', 'asd', 5, '2025-07-24', 'pending', '2025-07-08 01:32:56'),
(15, 4, 'aaa', 'aaa', 5, '0000-00-00', 'completed', '2025-07-08 02:36:19'),
(16, 4, 'b', 'b', 3, '0000-00-00', 'completed', '2025-07-08 02:40:21');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`) VALUES
(1, 'abl', 'abil24ti@mahasiswa.pcr.ac.id', '$2y$10$VTzc82PapIxupigucMBX2OhqQudYCqr3ojBwVAA1E5YbuaHJ/qdF2', 'user'),
(2, 'aar', 'abil24ti@mahasiswa.pcr.ac.id', '$2y$10$mVaOvi89w36TnX89mBZbQeTp/Wu0kljJIHvHnMTJkOmFxlBeUiHW2', 'user'),
(3, 'jny', 'abil24ti@mahasiswa.pcr.ac.id', '$2y$10$gSX/tHo0qyaN8F/QidhcpuZ1IGb4ZvuAHHFA.atlnu1bTuR7HeFwe', 'user'),
(4, 'andika', 'andika24ti@mahasiswa.pcr.ac.id', '$2y$10$mQWigkfm/vprrngXgUxFcuB0vEyKBqN2BFOWZgLhnsnnr0jYgi5YK', 'user'),
(5, 'admin', 'admin@example.com', '$2y$10$QZg6QWPqDobVvMtAOIaXb.OMKK3mWh55.b1raE15xxVjjirDq0faW', 'admin'),
(8, 'andika1', 'asfasf@afasf', '$2y$10$b8fUnlR5mGXqtfLhcHOSM.kEeGmQHUQMP9Kvc.WpGy1xJ2LqxjYD.', 'user');

-- --------------------------------------------------------

--
-- Struktur untuk view `completed_todos`
--
DROP TABLE IF EXISTS `completed_todos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `completed_todos`  AS SELECT `t`.`id` AS `id`, `t`.`title` AS `title`, `u`.`username` AS `username`, `t`.`status` AS `status` FROM (`todos` `t` join `users` `u` on(`t`.`user_id` = `u`.`id`)) WHERE `t`.`status` = 'completed' ;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `todos`
--
ALTER TABLE `todos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `todos`
--
ALTER TABLE `todos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `todos`
--
ALTER TABLE `todos`
  ADD CONSTRAINT `todos_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
