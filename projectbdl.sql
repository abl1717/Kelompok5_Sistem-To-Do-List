-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 10 Jul 2025 pada 01.03
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
    DECLARE avg_val DECIMAL(10, 2);

    -- Menghitung rata-rata tugas yang diselesaikan per hari yang berbeda
    -- Ini dilakukan dengan:
    -- 1. Subquery menghitung jumlah tugas yang diselesaikan untuk setiap hari unik (daily_completed_tasks)
    -- 2. Fungsi AVG() kemudian menghitung rata-rata dari nilai-nilai daily_completed_tasks tersebut.
    -- 3. IFNULL digunakan untuk memastikan mengembalikan 0.00 jika tidak ada tugas yang diselesaikan,
    --    karena AVG() akan mengembalikan NULL untuk set data kosong.
    SELECT IFNULL(AVG(daily_completed_tasks), 0.00)
    INTO avg_val
    FROM (
        SELECT COUNT(id) AS daily_completed_tasks
        FROM todos
        WHERE status = 'completed'
          AND user_id = userId
        GROUP BY DATE(created_at)
    ) AS daily_counts; -- Alias untuk subquery

    RETURN avg_val;
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
(16, 4, 'b', 'b', 3, '0000-00-00', 'completed', '2025-07-08 02:40:21'),
(17, 4, 'masak', 'masak rendang', 5, '2025-07-11', 'pending', '2025-07-09 22:26:18');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

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
