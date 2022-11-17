<?php

namespace api\modules\v1\models\definitions;

/**
 * @SWG\Definition(required={"firstname", "email"})
 *
 * @SWG\Property(property = "status", type = "boolean", example = "1"),
 * @SWG\Property(property = "message", type = "string", example = "success"),
 * @SWG\Property(property = "payload", type = "object",
 *         @SWG\Property(property = "id", type = "integer", example = "101"),
 *         @SWG\Property(property = "firstname", type = "string", example = "John"),
 *         @SWG\Property(property = "lastname", type = "string", example = "doe"),
 *         @SWG\Property(property = "email", type = "string", example = "abc@gmail.com"),
 *         @SWG\Property(property = "job_title", type = "string", example = "Software Engineer"),
 *         @SWG\Property(property = "mobile_number", type = "integer", example = 123456789),
 *         @SWG\Property(property = "phone_number", type = "integer", example = 01126026347),
 *         @SWG\Property(property = "company", type = "string", example = "handysolver"),
 *         @SWG\Property(property = "website", type = "string", example = "https://handysolver.com/"),
 *         @SWG\Property(property = "notes", type = "string", example = "abcde"),
 *         @SWG\Property(property = "address", type = "string", example = "18/24 M-Block Gk"),
 *         @SWG\Property(property = "imageUrl", type = "string", example = "https://test.crm.lookingforwardconsulting.com/storage/web/source/hp5uvh.jpeg?nocache=1668666307"),
 *         @SWG\Property(property = "birthday", type = "date", example = "29-01-1998"),
 *         @SWG\Property(property = "pollguru", type = "integer", example = "0"),
 *         @SWG\Property(property = "buzz", type = "integer", example = "0"),
 *         @SWG\Property(property = "learning_arcade", type = "integer", example = "1"),
 *         @SWG\Property(property = "training_pipeline", type = "integer", example = "0"),
 *         @SWG\Property(property = "leadership_edge", type = "integer", example = "1"),
 *         @SWG\Property(property = "created_by", type = "integer", example = "11"),
 *         @SWG\Property(property = "code", type = "string", example = "demo07"),
 *         @SWG\Property(property = "city", type = "string", example = "new delhi"),
 *         @SWG\Property(property = "state", type = "string", example = "delhi"),
 *         @SWG\Property(property = "country", type = "string", example = "india"),
 *        @SWG\Property(property = "address_type", type = "string", example = "Personal"),
 *        @SWG\Property(property = "pincode", type = "string", example = "110019"),
 *        @SWG\Property(property = "lead_id", type = "string", example = "CRM-LEAD-2022-12345"),
 *
 *   )

 */
class Contact
{

}