<?php

class AuditLog extends BaseModel
{
    protected function table(): string
    {
        return 'audit_logs';
    }
}

