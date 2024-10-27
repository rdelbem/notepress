Notepress WordPress Plugin
==========================

A note-taking application with workspace management, built as a WordPress plugin using React, Redux, TypeScript, and PHP.

Table of Contents
-----------------

-   [Description](#description)
-   [Features](#features)
-   [Technologies Used](#technologies-used)
-   [Prerequisites](#prerequisites)
-   [Installation](#installation)
-   [Usage](#usage)
-   [Project Structure](#project-structure)
-   [Development](#development)
-   [Running Tests](#running-tests)
-   [Contributing](#contributing)
-   [License](#license)

Description
-----------

Notepress is a WordPress plugin that transforms your WordPress site into a powerful note-taking application. **It will take control over your WP instance**. It leverages React for the frontend interface and WordPress for backend management, providing a seamless experience for users to create, edit, and manage notes within different workspaces.

Features
--------

-   **Workspace Management**: Organize your notes into various workspaces for better categorization.
-   **Note Creation and Editing**: Create, edit, and delete notes with a rich text editor.
-   **Pagination**: Navigate through notes and workspaces easily with pagination support.
-   **User Authentication**: Secure access using JWT authentication integrated with WordPress user roles.
-   **Responsive Design**: Mobile-friendly interface built with `styled-components`.
-   **State Management**: Efficient state handling using Redux and Redux Thunk.
-   **Routing**: Client-side routing with React Router, integrated with WordPress routing.
-   **Unit Testing**: Comprehensive testing with Jest and React Testing Library.

Technologies Used
-----------------

-   **Frontend**:
    -   React
    -   TypeScript
    -   Redux & Redux Thunk
    -   React Router
    -   styled-components
    -   React Top Loading Bar
-   **Backend**:
    -   WordPress (PHP)
    -   Firebase JWT for authentication
-   **Testing**:
    -   Jest
    -   React Testing Library
    -   Codecept
    -   WP Browser
    -   PHPUnit
-   **Build Tools**:
    -   Webpack
    -   Babel

Prerequisites
-------------

-   **WordPress**: Version 5.0 or higher.
-   **PHP**: 8.1 or higher.
-   **Node.js**: Version 18 or higher.
-   **npm**: Node package manager.

Installation
------------

### 1\. Clone the Repository

### 2\. Install Node Dependencies

`npm install`

### 3\. Build the React Application

`npm run build`

This will compile the React application and place the bundled files in the appropriate directory for the WordPress plugin.

### 4\. Install the Plugin in WordPress

1.  **Copy the Plugin Folder**: Copy the entire `notepress-wordpress-plugin` directory into your WordPress `wp-content/plugins/` directory.
2.  **Activate the Plugin**: Log in to your WordPress admin dashboard, navigate to **Plugins**, find **Notepress**, and click **Activate**.

### 5\. Permalink Settings

Ensure that your WordPress permalink structure is set to `Post name`:

1.  Navigate to **Settings** > **Permalinks**.
2.  Select **Post name**.
3.  Click **Save Changes**.

Usage
-----

After activating the plugin:

1.  **Access Notepress**: A new menu item **Notepress** will appear in your WordPress admin dashboard or the admin navbar.
2.  **Create Workspaces**: Start by creating workspaces to organize your notes.
3.  **Manage Notes**: Create, edit, and delete notes within your workspaces.

Project Structure
-----------------

-   **react-app/**: Contains the React frontend application.
-   **src/**: Contains the PHP source code for the WordPress plugin.
    -   **Api/**: API endpoints for notes and workspaces.
    -   **AppRootView/**: Base template for rendering the React application within WordPress.
    -   **Types/**: PHP classes defining data structures (Note, Workspace, Author).
    -   **Util/**: Utility traits and classes.
    -   **Activation.php**: Handles plugin activation tasks.
    -   **Auth.php**: Manages JWT authentication.
    -   **CoreLoader.php**: Core functionality loader.
    -   **Indexation.php**: Indexing for notes and workspaces.
    -   **PostTypeAndTaxonomy.php**: Registers custom post types and taxonomies.
    -   **routes.php**: Defines REST API routes.
-   **olmec-notepress.php**: Main plugin file that initializes the plugin.
-   **package.json**: Contains Node.js dependencies and scripts.

Development
-----------

### Setting Up Development Environment

1.  **Install Dependencies**: Ensure all Node.js dependencies are installed with `npm install`. Also, remember to run `composer install`.

2.  **Start Development Server**:

    `docker compose up -d`

    `npm start`

    This will start a development server with hot reloading at `http://localhost:3000`.

3.  **Build for Development**:

    `npm run build`

    This builds the React application.

### WordPress Environment

There is a docker compose file here, and running it will provide all WP server side features needed for this project.

### WP-CLI

The plugin includes WP-CLI commands located in the `src/WPCLI` directory for tasks like data migration or cleanup.

Running Tests
-------------

To run the unit tests:

`composer test`

`npm test`

Contributing
------------

Contributions are welcome! Please follow these steps:

1.  **Fork the Repository**: Click the **Fork** button at the top right of the repository page.

2.  **Clone Your Fork**:

3.  **Create a New Branch**:

    `git checkout -b feature/your-feature-name`

4.  **Make Your Changes**: Implement your feature or bug fix.

5.  **Commit Your Changes**:

    `git commit -m "Add your feature"`

6.  **Push to Your Branch**:

    `git push origin feature/your-feature-name`

7.  **Create a Pull Request**: Open a pull request to the main repository's `develop` branch.

License
-------

This project is licensed under the MIT License. See the <LICENSE> file for details.