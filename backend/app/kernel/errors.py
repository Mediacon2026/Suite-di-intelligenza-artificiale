from __future__ import annotations

from typing import Any


class KernelError(Exception):
    error_code = "KERNEL_ERROR"

    def __init__(self, message: str, details: dict[str, Any] | None = None):
        super().__init__(message)
        self.message = message
        self.details = details or {}

    def to_dict(self) -> dict[str, Any]:
        return {
            "error_code": self.error_code,
            "message": self.message,
            "details": self.details,
        }


class ValidationError(KernelError):
    error_code = "VALIDATION_ERROR"


class RuleEvaluationError(KernelError):
    error_code = "RULE_EVALUATION_ERROR"


class WorkflowError(KernelError):
    error_code = "WORKFLOW_ERROR"


class DocumentError(KernelError):
    error_code = "DOCUMENT_ERROR"


class NotificationError(KernelError):
    error_code = "NOTIFICATION_ERROR"


class AuditError(KernelError):
    error_code = "AUDIT_ERROR"
