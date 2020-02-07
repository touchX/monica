<?php

namespace App\Helpers;

use App\Models\Contact\Contact;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AuditLogHelper
{
    /**
     * Prepare a collection of audit logs that is displayed on the Settings page.
     *
     * @param mixed $logs
     *
     * @return Collection
     */
    public static function getCollectionOfAuditForSettings($logs): Collection
    {
        $logsCollection = collect();

        foreach ($logs as $log) {
            // the log is about a contact
            if (isset($log->object->{'contact_id'})) {
                try {
                    // check if the contact that the log is about still exists
                    // in that case, we will display a link to point to this contact
                    $contact = Contact::findOrFail($log->object->{'contact_id'});
                    $description = trans(
                        'app.log_'.$log->action.'_with_name_with_link',
                        [
                            'link' => '/people/'.$contact->hashId(),
                            'name' => $contact->name,
                        ]
                    );
                } catch (ModelNotFoundException $e) {
                    // the contact doesn't exist anymore, we don't need a link
                    $description = trans('app.log_'.$log->action.'_with_name', ['name' => $log->object->{'contact_name'}]);
                }

                $logsCollection->push([
                    'author_name' => ($log->author) ? $log->author->name : $log->author_name,
                    'description' => $description,
                    'audited_at' => DateHelper::getShortDateWithTime($log->audited_at),
                ]);
            }
        }

        return $logsCollection;
    }
}
