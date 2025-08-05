# Sistema de Recordatorios - Setup

## Testing (1 minuto)

```bash
cd projects/web
php bin/console app:send-reminders
```

## Producción (1 mes)

### 1. Cambiar tiempo en ReminderService.php

```php
$cutoffDate = new \DateTimeImmutable('-1 month');
```

### 2. Configurar cron

```bash
crontab -e
# Agregar: 0 9 * * * cd /ruta/proyecto/projects/web && php bin/console app:send-reminders >> var/log/reminders.log 2>&1
```

### 3. Verificar

```bash
crontab -l
tail -f var/log/reminders.log
```

## Cron - Frecuencias

### Diario a las 9:00 AM

```bash
0 9 * * * cd /ruta/proyecto/projects/web && php bin/console app:send-reminders >> var/log/reminders.log 2>&1
```

### Cada domingo a las 9:00 AM

```bash
0 9 * * 0 cd /ruta/proyecto/projects/web && php bin/console app:send-reminders >> var/log/reminders.log 2>&1
```

### Cada 6 horas

```bash
0 */6 * * * cd /ruta/proyecto/projects/web && php bin/console app:send-reminders >> var/log/reminders.log 2>&1
```

### Solo días laborables (L-V)

```bash
0 9 * * 1-5 cd /ruta/proyecto/projects/web && php bin/console app:send-reminders >> var/log/reminders.log 2>&1
```

## Comandos útiles

```bash
# Ejecutar manualmente
php bin/console app:send-reminders

# Ver logs
tail -f var/log/dev.log
tail -f var/log/reminders.log

# Script de testing
./test_reminders.sh
```
