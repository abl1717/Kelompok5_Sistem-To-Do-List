CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100)
);

CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    title VARCHAR(255),
    description TEXT,
    deadline DATE,
    completed BOOLEAN DEFAULT FALSE,
    completed_at DATETIME DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE VIEW view_completed_tasks AS
SELECT u.name, t.title, t.completed_at
FROM users u JOIN tasks t ON u.id = t.user_id
WHERE t.completed = TRUE;

DELIMITER //
CREATE TRIGGER trg_task_completed
BEFORE UPDATE ON tasks
FOR EACH ROW
BEGIN
    IF NEW.completed = TRUE AND OLD.completed = FALSE THEN
        SET NEW.completed_at = NOW();
    END IF;
END;
//
DELIMITER ;

DELIMITER //
CREATE PROCEDURE add_task(
    IN p_user_id INT,
    IN p_title VARCHAR(255),
    IN p_description TEXT,
    IN p_deadline DATE
)
BEGIN
    INSERT INTO tasks (user_id, title, description, deadline)
    VALUES (p_user_id, p_title, p_description, p_deadline);
END;
//
DELIMITER ;