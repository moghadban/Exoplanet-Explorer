
# 🚀 Exoplanet Explorer: Installation & Setup Guide

The **Exoplanet Explorer** is a modern, API-driven dashboard designed for astronomers and space enthusiasts to visualize and analyze data on newly discovered worlds. Built with the **Symfony Framework**, this application consumes external data sources to provide a rich, interactive interface for filtering, searching, and viewing detailed profiles of exoplanets. It’s a powerful tool for exploring astronomical data and understanding the characteristics of distant planetary systems.

## 🌟 Data Source

The exoplanet data displayed in this application is **Powered by the NASA Exoplanet Archive**.

**Link:** <https://exoplanetarchive.ipac.caltech.edu/cgi-bin/TblView/nph-tblView?app=ExoTbls&config=PS>

---

Welcome to the Exoplanet Explorer! This guide will walk you through setting up your local environment, installing dependencies, and running the necessary developer tools to contribute to the project.

## 🛠️ Prerequisites

Before you begin, ensure your local development environment meets the following requirements:

| Component | Minimum Version | Check Command | 
 | ----- | ----- | ----- | 
| **PHP** | `8.2` or above | `php -v` | 
| **Composer** | Latest Stable | `composer -v` | 
| **Git** | Latest Stable | `git -v` | 
| **Symfony CLI** | Latest Stable | `symfony -v` |
## 1. Local Setup

### 1.1. Clone the Repository

Start by cloning the Exoplanet Explorer repository to your local machine using Git:

    # Clone the repository
    git clone [https://github.com/moghadban/Exoplanet-Explorer.git](https://github.com/moghadban/Exoplanet-Explorer.git)
    
    # Navigate into the project directory
    cd Exoplanet-Explorer
    
    
    
    

### 1.2. Install PHP Dependencies (Composer)

We use **Composer** to manage all project dependencies, including the Symfony framework components and developer tools like PHPUnit and PHPStan.

Run the following command in the project's root directory:

    composer install
    
    
    
    

**Interpretation:**

*   This command reads the `composer.json` file.
    
*   It downloads all required libraries (listed under `require`) and development tools (listed under `require-dev`) into the `./vendor` directory.
    

## 2\. Running the Application

Since this is an API-driven application, no database setup is required. You can jump straight into running the Symfony web server.

We strongly recommend using the **Symfony CLI** for the built-in web server, as it is optimized for Symfony applications and provides HTTPS support automatically.

    symfony serve
    
    
    
    

*   **Access:** Your application will typically be available at `https://127.0.0.1:8000`.
    
*   **Stop:** Press `Ctrl+C` in the terminal to stop the server.
    

## 3\. Developer Tooling & Scripts

Your project utilizes several Composer scripts to automate quality assurance tasks. You can run these tools directly from the command line using `composer run-script <script-name>` or the shorthand `composer <script-name>`.

### 3.1. Running Tests (`test`)

The `test` script runs PHPUnit, our testing framework, using the `--testdox` flag for readable output.

    composer test
    
    
    
    

The `--testdox` flag converts cryptic test method names (e.g., `testUserLoginWithValidCredentials`) into descriptive sentences (**"A user can log in with valid credentials"**). This is highly user-friendly.

### 3.2. Code Style Checking (`phpcs`) and Fixing (`phpcbf`)

We use PHP Code Sniffer to enforce coding standards.

1.  **Check for violations (Report Only):**
    
        composer phpcs
        
        
        
        
        
    
2.  **Automatically Fix violations (Modifies Files):**
    
        composer phpcbf
        
        
        
        
        
    
    **⚠️ Important:** Always inspect a `git diff` after running `phpcbf` to ensure the automatic fixes are correct before committing!
    

### 3.3. Static Analysis (`phpstan`)

PHPStan checks for potential bugs and type-related issues in your code without executing it.

    composer phpstan
    
    
    
    

This is essential for catching errors that might slip past PHP's runtime checks.

### 3.4. The Ultimate Quality Check (`analyze`)

The `analyze` script is a **pipeline** that runs all critical quality checks sequentially. **This is the script you should run before every commit.**

    composer analyze
    
    
    
    

**The `analyze` script executes in this order:**

1.  **`@test`** (Runs all PHPUnit tests)
    
2.  **`@phpcs`** (Checks code style)
    
3.  **`@phpcbf`** (Automatically fixes most code style issues)
    
4.  **`@phpstan`** (Performs static code analysis)
    

By running `composer analyze`, you ensure your code is fully tested, adheres to style standards, and is free of common type bugs, guaranteeing a high level of code quality.
