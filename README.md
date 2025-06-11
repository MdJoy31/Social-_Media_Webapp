# Social-_Media_Webapp
A simple PHP &amp; MySQL web application that demonstrates user registration, authentication, and friend-management features with pagination and mutual-friend counts.


````markdown
# My Friend System

A simple PHP & MySQL web application that demonstrates user registration, authentication, and friend‐management features with pagination and mutual‐friend counts, styled with modern CSS.

## 🚀 Features

- **User Registration & Login**  
  - Sign up with email, profile name, and password  
  - Session-based authentication  

- **Friend List**  
  - View your current friends in an alphabetical list  
  - “Unfriend” button removes a friend and updates your friend count  
  - Displays the number of mutual friends for each connection  
  - Pagination (5 friends per page) with “Previous” / “Next” controls  

- **Add Friends**  
  - Browse all registered users (excluding yourself and existing friends)  
  - “Add as friend” button adds a new connection and updates friend counts  
  - Shows mutual-friend counts on each suggestion  
  - Pagination (5 suggestions per page) with “Previous” / “Next” controls  

- **About Page**  
  - Accordion-style report describing completed tasks, challenges, special features, and discussion-board participation  
  - Quick links to Home, Friend List, and Add Friends pages  

## 🛠 Tech Stack

- **Backend:** PHP  
- **Database:** MySQL  
- **Styling:** CSS (Flexbox & custom variables)  
- **Frontend:** HTML5, minimal JavaScript  

## 📦 Installation

1. **Clone the repository**  
   ```bash
   git clone https://github.com/<your-username>/my-friend-system.git
   cd my-friend-system
````

2. **Configure your database**

   * Create a database named `s<your-7digitID>_db`
   * On first run, the app auto-creates two tables:

     * `friends` (stores user accounts)
     * `myfriends` (stores friendship relations)
   * Edit `db_config.php` with your DB host, user, password, and database name.

3. **Deploy**

   * Copy files to your PHP-enabled web server’s document root.
   * Ensure `images/image.png` is in the `images/` folder.
   * Visit 'https://mercury.swin.edu.au/cos30020/s103799644/assign3/index.php'.

4. **Use**

   * **Register** a new account via Sign Up
   * **Log In**, then manage connections on **Friend List** and **Add Friends**

## 🔐 Future Improvements

* Secure password hashing (e.g. `password_hash`)
* CSRF protection on all forms
* Profile pictures, direct messaging, real-time notifications
* Role-based access control and enhanced input sanitization

---

© 2025 Md Jannatul Rakib Joy

```
```
