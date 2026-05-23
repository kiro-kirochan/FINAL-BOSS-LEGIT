import React from 'react';

// Reusable Delete Confirmation Modal — used by all three modules
// Returns null (renders nothing) when show=false
export default function DeleteModal({ show, recordName, recordType = 'record', onConfirm, onCancel }) {
    if (!show) return null;

    return (
        <div
            className="modal show d-block"
            tabIndex="-1"
            style={{ backgroundColor: 'rgba(0,0,0,0.55)' }}
        >
            <div className="modal-dialog modal-dialog-centered">
                <div className="modal-content shadow-lg">
                    <div className="modal-header border-0 pb-0">
                        <h5 className="modal-title text-danger fw-bold">
                            <i className="fa-solid fa-triangle-exclamation me-2"></i>
                            Confirm Deletion
                        </h5>
                        <button className="btn-close" onClick={onCancel}></button>
                    </div>

                    <div className="modal-body py-3">
                        <p className="mb-0 text-muted">
                            Are you sure you want to permanently delete the {recordType}{' '}
                            <strong className="text-dark">"{recordName}"</strong>?
                            This action <strong>cannot be undone</strong>.
                        </p>
                    </div>

                    <div className="modal-footer border-0 pt-0">
                        <button className="btn btn-light" onClick={onCancel}>
                            <i className="fa-solid fa-xmark me-1"></i> Cancel
                        </button>
                        <button className="btn btn-danger" onClick={onConfirm}>
                            <i className="fa-solid fa-trash-can me-1"></i> Confirm Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}
