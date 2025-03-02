CREATE TABLE `UserBooks`(
    `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `modified` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `userID` int,
    `bookID` int,
    FOREIGN KEY (`bookID`) REFERENCES `Books` (`id`),
    FOREIGN KEY (`userID`) REFERENCES `Users` (`id`),
    UNIQUE KEY `saved_book` (`userID`, `bookID`)
)