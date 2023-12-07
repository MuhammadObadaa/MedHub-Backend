Write-Host "Welcome to MedHub DataBase insertion automation v1.0.0"  -ForegroundColor DarkYellow
$choice = 0
$imageInputMessage = "input medicine image (just its name and extension like 'image.png' _make sure it's in /app directory in laravel project_ or just leave it empty)"
while ($choice -eq 0) {
    while ($choice -lt 1 -or $choice -gt 3) {
        Write-Host "Choose (by number) model to insert :" -ForegroundColor DarkYellow
        Write-Host "1)medicine   2)category   3)exit " -ForegroundColor DarkCyan
        $choice = Read-Host
    }
    if ($choice -eq 3) {
        break
    }
    elseif ($choice -eq 1) {
        $name = Read-Host -Prompt 'input medicine name '
        $ar_name = Read-Host -Prompt 'input medicine ar_name '
        $scientificName = Read-Host -Prompt 'input medicine scientificName '
        $ar_scientificName = Read-Host -Prompt 'input medicine ar_scientificName '
        $category_id = Read-Host -Prompt 'input medicine category_id '
        $brand = Read-Host -Prompt 'input medicine brand '
        $description = Read-Host -Prompt 'input medicine description '
        $ar_description = Read-Host -Prompt 'input medicine ar_description '
        $quantity = Read-Host -Prompt 'input medicine quantity '
        $price = Read-Host -Prompt 'input medicine price '
        $expirationDate = Read-Host -Prompt 'input medicine expiration Date (yyyy-MM-dd)'
        $image = Read-Host -Prompt $imageInputMessage
        Write-Host $image
        if ($image) {
            $image = 'app/' + $image
        }
        $created_at = Get-Date -Format 'yyyy-M-dd'
        $query = "use medhub;INSERT INTO medicines(name, ar_name, scientificName, ar_scientificName, category_id, brand, description, ar_description, quantity, expirationDate, price, image, created_at, updated_at)
        VALUES ('$name','$ar_name','$scientificName','$ar_scientificName','$category_id','$brand','$description','$ar_description','$quantity','$expirationDate','$price','$image','$created_at','$created_at')"
        mysql -u root -e $query
    }
    else {
        $name = Read-Host -Prompt 'input category name '
        $ar_name = Read-Host -Prompt 'input category ar_name '
        $created_at = Get-Date -Format 'yyyy-M-dd'
        $query = "use medhub;INSERT INTO categories(name, ar_name,created_at, updated_at)
        VALUES ('$name','$ar_name','$created_at','$created_at')"
        mysql -u root -e $query
    }
    if ($?) {#checks the last command if it was executed successfully
        Write-Host "Recorded!" -ForegroundColor DarkGreen
    }
    else {
        Write-Host "Something went wrong!" -ForegroundColor DarkRed
    }
    $choice = 0
}

Write-Host "Thank you for using my automation .. go and check what the hack you've just made with your DB :)" -ForegroundColor DarkGreen


# Muhammad Obadaa Almasri 2023
