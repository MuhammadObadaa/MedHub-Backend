# MedHub-Backend
---

## About :
A backend system to handle orders between pharmacists and a warehouse owner and provide statistics and charts for both built with Laravel 10

The frontend side project: [Web application for **warehouse owner**](https://github.com/Abdalrahman-Alhamod/MedHub-Web), [Mobile application for **pharmacists**](https://github.com/Abdalrahman-Alhamod/MedHub-Mobile)


## By:
[**Muhammad Obadaa Almasri**](https://github.com/MuhammdObadaa)

[**Muhammad Yassen**](https://github.com/MhdYa9)

[**Abdalrahman Alhamod**](https://github.com/Abdalrahman-Alhamod)

---
## Features :
* **Order management** : pharmacists can create, view and update. Admin can view, accept, or reject orders.

* **Medicine management** : admin can create, view, update, and delete medicines. Pharmacists can view medicines and their details.

* **Statistics** : the system provides many statistics and charts for both admin and pharmacists.

* **Reports** : the system generates PDF reports for orders and medicines, which can be downloaded by the admin and pharmacists.

* **Notifications** : the system uses Firebase to push notifications to the pharmacists when their orders are accepted or rejected by the admin. and to the admin once a new order created.

## Technologies and packages :
* php (Laravel)
* MySQL
* Laravel blade
* Firebase
* DomPdf

---

## Installation :
To install the project, Make sure that php, composer and mysql are installed then follow these steps:

1. ### Clone this repository :

```bash
    git clone https://github.com/MuhammdObadaa/MedHub-Backend.
```
2. ### Go to the project directory : 
```bash
    cd MedHub-Backend
```
3. ### Install the dependencies and prepare the project:
    make sure that gd extension in php.ini is enabled by removing `;` in the first of line `;extension=gd`. then:
```bash
    composer install
    cp .env.example .env
    php artisan key:generate
    composer require barryvdh/laravel-dompdf
    php artisan migrate
```
4. ### Set .env file and add your MySQL and Firebase credentials

5. ### Run the schedule and Start the serve:
```bash
    php artisan schedule:run
    php artisan serve
```

Once you log in, you can access the features of the system according to your role. You can use Postman to test the API endpoints. or use the frontend projects mentioned above.

---

### Contributing :
If you want to contribute to this project, you can fork this repository and make a pull request. You can also open an issue if you find any bugs or have any suggestions.

### License :
This project is licensed under the MIT License. See the LICENSE file for more details.

