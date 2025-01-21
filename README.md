## Instruction

- `composer install`
- `php artisan migrate:fresh --seed`
- `php artisan ips:renew path/to/file.csv`
- `php artisan storage:link`
- `php artisan queue:listen` (daemon)
- `php artisan schedule:work` (daemon)
- `npm install`
- `npm run production`

### ELB

- sudo chown
- chmod -R 775 storage

#### OR

- `composer.phar install`
- `sudo yum install -y gcc-c++ make`
- `curl -sL https://rpm.nodesource.com/setup_16.x | sudo -E bash -`
- `sudo yum install -y nodejs`
- link RDS VPC to EC2 instance