# task-liquibase
The LiquibaseTask is a generic task for liquibase commands that don't require extra command parameters. You can run commands like updateSQL, validate or updateTestingRollback with this task but not rollbackToDateSQL since it requires a date parameter after the command.
