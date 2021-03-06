<?php

require_once(dirname(__FILE__).'/../config.php');

class ActionMailer_TestCase extends ActionMailerUnitTest
{
    public function encode($text, $charset = 'utf-8') {
        return AkActionMailerQuoting::quotedPrintable($text, $charset);
    }

    public function &new_mail($charset = 'utf-8') {
        $Mail = new AkMailMessage();
        $Mail->setMimeVersion('1.0');
        $Mail->setContentType('text/plain; charset:'.$charset);
        return $Mail;

    }

    public function setup() {
        $this->Mailer = new AkActionMailer();
        $this->Mailer->delivery_method = 'test';
        $this->Mailer->perform_deliveries = true;
        $this->Mailer->deliveries = array();
        $this->recipient = 'test@localhost';
    }

    public function test_nested_parts() {
        $TestMailer = new TestMailer();
        $Created = $TestMailer->create('nested_multipart', $this->recipient);


        $this->assertEqual(2, count($Created->parts));
        $this->assertEqual(2, count($Created->parts[0]->parts));
        $this->assertEqual( "multipart/mixed", $Created->content_type);
        $this->assertEqual( "multipart/alternative", $Created->parts[0]->content_type );
        $this->assertEqual( "bar", $Created->parts[0]->getHeader('Foo') );
        $this->assertEqual( "akmailpart", strtolower(get_class($Created->parts[0]->parts[0])));
        $this->assertEqual( "text/plain", $Created->parts[0]->parts[0]->content_type );

        $this->assertEqual( "text/html", $Created->parts[0]->parts[1]->content_type );
        $this->assertEqual( "application/octet-stream", $Created->parts[1]->content_type );

    }

    public function test_attachment_with_custom_header() {
        $TestMailer = new TestMailer();
        $Created = $TestMailer->create('attachment_with_custom_header', $this->recipient);
        $this->assertEqual( "<test@test.com>", $Created->parts[1]->getHeader('Content-ID'));
    }


    public function test_signed_up() {
        $Expected = $this->new_mail();
        $Expected->setTo($this->recipient);
        $Expected->setSubject("[Signed up] Welcome $this->recipient");
        $Expected->setBody("Hello there,\n\nMr. $this->recipient");
        $Expected->setFrom("system@example.com");
        $Expected->setDate(Ak::getTimestamp("2004-12-12"));


        $TestMailer = new TestMailer();
        $this->assertTrue($Created = $TestMailer->create('signed_up', $this->recipient));
        $this->assertEqual($Expected->getEncoded(), $Created->getEncoded());

        $this->assertTrue($TestMailer->deliver('signed_up', $this->recipient));
        $this->assertTrue(!empty($TestMailer->deliveries[0]));
        $this->assertEqual($Expected->getEncoded(), $TestMailer->deliveries[0]);

    }


    public function test_custom_template() {
        $Expected = $this->new_mail();
        $Expected->setTo($this->recipient);
        $Expected->setSubject("[Signed up] Welcome $this->recipient");
        $Expected->setBody("Hello there,\n\nMr. $this->recipient");
        $Expected->setFrom("system@example.com");

        $TestMailer = new TestMailer();
        $this->assertTrue($Created = $TestMailer->create('custom_template', $this->recipient));
        $this->assertEqual($Expected->getEncoded(), $Created->getEncoded());

    }

    public function test_cancelled_account() {
        $Expected = $this->new_mail();
        $Expected->setTo($this->recipient);
        $Expected->setSubject("[Cancelled] Goodbye $this->recipient");
        $Expected->setBody("Goodbye, Mr. $this->recipient");
        $Expected->setFrom("system@example.com");
        $Expected->setDate("2004-12-12");

        $TestMailer = new TestMailer();
        $this->assertTrue($Created = $TestMailer->create('cancelled_account', $this->recipient));

        $this->assertEqual($Expected->getEncoded(), $Created->getEncoded());

        $this->assertTrue($TestMailer->deliver('cancelled_account', $this->recipient));
        $this->assertTrue(!empty($TestMailer->deliveries[0]));
        $this->assertEqual($Expected->getEncoded(), $TestMailer->deliveries[0]);
    }


    public function test_cc_bcc() {
        $Expected = $this->new_mail();
        $Expected->setTo($this->recipient);
        $Expected->setSubject("testing bcc/cc");
        $Expected->setBody("Nothing to see here.");
        $Expected->setFrom("system@example.com");
        $Expected->setDate("2004-12-12");
        $Expected->setCc("nobody@example.com");
        $Expected->setBcc("root@example.com");


        $TestMailer = new TestMailer();
        $this->assertTrue($Created = $TestMailer->create('cc_bcc', $this->recipient));

        $this->assertEqual($Expected->getEncoded(), $Created->getEncoded());

        $this->assertTrue($TestMailer->deliver('cc_bcc', $this->recipient));
        $this->assertTrue(!empty($TestMailer->deliveries[0]));
        $this->assertEqual($Expected->getEncoded(), $TestMailer->deliveries[0]);
    }



    public function test_iso_charset() {
        $Expected = $this->new_mail();
        $Expected->setTo($this->recipient);
        $Expected->setCharset("ISO-8859-1");
        $Expected->setSubject(Ak::recode('testing isø charsets','ISO-8859-1', 'UTF-8'));
        $Expected->setBody("Nothing to see here.");
        $Expected->setFrom("system@example.com");
        $Expected->setDate("2004-12-12");
        $Expected->setCc("nobody@example.com");
        $Expected->setBcc("root@example.com");

        $TestMailer = new TestMailer();

        $this->assertTrue($Created = $TestMailer->create('iso_charset', $this->recipient));

        $this->assertEqual($Expected->getEncoded(), $Created->getEncoded());

        $this->assertTrue($TestMailer->deliver('iso_charset', $this->recipient));
        $this->assertTrue(!empty($TestMailer->deliveries[0]));
        $this->assertEqual($Expected->getEncoded(), $TestMailer->deliveries[0]);

        $this->assertEqual($Created->getSubject(), '=?ISO-8859-1?Q?testing_is=F8_charsets?=');

    }


    public function test_unencoded_subject() {
        $Expected = $this->new_mail();
        $Expected->setTo($this->recipient);
        $Expected->setSubject("testing unencoded subject");
        $Expected->setBody("Nothing to see here.");
        $Expected->setFrom("system@example.com");
        $Expected->setDate("2004-12-12");
        $Expected->setCc("nobody@example.com");
        $Expected->setBcc("root@example.com");

        $TestMailer = new TestMailer();

        $this->assertTrue($Created = $TestMailer->create('unencoded_subject', $this->recipient));

        $this->assertEqual($Expected->getEncoded(), $Created->getEncoded());

        $this->assertTrue($TestMailer->deliver('unencoded_subject', $this->recipient));
        $this->assertTrue(!empty($TestMailer->deliveries[0]));
        $this->assertEqual($Expected->getEncoded(), $TestMailer->deliveries[0]);

        $this->assertEqual($Created->getSubject(), 'testing unencoded subject');
    }


    public function test_perform_deliveries_flag() {
        $TestMailer = new TestMailer();

        $TestMailer->perform_deliveries = false;
        $this->assertTrue($TestMailer->deliver('signed_up', $this->recipient));
        $this->assertEqual(count($TestMailer->deliveries), 0);

        $TestMailer->perform_deliveries = true;
        $this->assertTrue($TestMailer->deliver('signed_up', $this->recipient));
        $this->assertEqual(count($TestMailer->deliveries), 1);

    }

    public function test_unquote_quoted_printable_subject() {
        $msg = <<<EOF
From: me@example.com
Subject: =?UTF-8?Q?testing_testing_=D6=A4?=
Content-Type: text/plain; charset=iso-8859-1

The body
EOF;

        $Mail = AkMailBase::parse($msg);
        $this->assertEqual("testing testing \326\244", $Mail->subject);
        $this->assertEqual("=?UTF-8?Q?testing_t?=\r\n =?UTF-8?Q?esting_=D6=A4?=",$Mail->getSubject('UTF-8'));

    }

    public function test_unquote_7bit_subject() {
        $msg = <<<EOF
From: me@example.com
Subject: this == working?
Content-Type: text/plain; charset=iso-8859-1

The body
EOF;

        $Mail = AkMailBase::parse($msg);
        $this->assertEqual("this == working?", $Mail->subject);
        $this->assertEqual("this == working?", $Mail->getSubject());

    }


    public function test_unquote_7bit_body() {
        $msg = <<<EOF
From: me@example.com
Subject: subject
Content-Type: text/plain; charset=iso-8859-1
Content-Transfer-Encoding: 7bit

The=3Dbody
EOF;

        $Mail = AkMailBase::parse($msg);
        $this->assertEqual("The=3Dbody", $Mail->body);
        $this->assertEqual("The=3Dbody", $Mail->getBody());

    }

    public function test_unquote_quoted_printable_body() {
        $msg = <<<EOF
From: me@example.com
Subject: subject
Content-Type: text/plain; charset=iso-8859-1
Content-Transfer-Encoding: quoted-printable

The=3Dbody
EOF;

        $Mail = AkMailBase::parse($msg);
        $this->assertEqual("The=body", $Mail->body);
        $this->assertEqual("The=3Dbody", $Mail->getBody());

    }

    public function test_unquote_base64_body() {
        $msg = <<<EOF
From: me@example.com
Subject: subject
Content-Type: text/plain; charset=iso-8859-1
Content-Transfer-Encoding: base64

VGhlIGJvZHk=
EOF;

        $Mail = AkMailBase::parse($msg);
        $this->assertEqual("The body", $Mail->body);
        $this->assertEqual("VGhlIGJvZHk=", $Mail->getBody());
    }

    public function test_extended_headers() {
        $this->recipient = "Grytøyr <test@localhost>";
        $Expected = $this->new_mail();
        $Expected->setTo($this->recipient);
        $Expected->setCharset("ISO-8859-1");
        $Expected->setSubject("testing extended headers");
        $Expected->setBody("Nothing to see here.");
        $Expected->setFrom("Grytøyr <stian1@example.com>");
        $Expected->setDate("2004-12-12");
        $Expected->setCc("Grytøyr <stian2@example.com>");
        $Expected->setBcc("Grytøyr <stian3@example.com>");

        $TestMailer = new TestMailer();

        $this->assertTrue($Created = $TestMailer->create('extended_headers', $this->recipient));

        $this->assertEqual($Expected->getEncoded(), $Created->getEncoded());

        $this->assertTrue($TestMailer->deliver('extended_headers', $this->recipient));
        $this->assertTrue(!empty($TestMailer->deliveries[0]));
        $this->assertEqual($Expected->getEncoded(), $TestMailer->deliveries[0]);
    }

    public function test_utf8_body_is_not_quoted() {
        $TestMailer = new TestMailer();
        $this->assertTrue($Created = $TestMailer->create('utf8_body', $this->recipient));
        $this->assertPattern('/åœö blah/', $Created->getBody());
    }

    public function test_multiple_utf8_recipients() {
        $this->recipient = array("\"Foo áëô îü\" <extended@example.com>", "\"Example Recipient\" <me@example.com>");
        $TestMailer = new TestMailer();
        $this->assertTrue($Created = $TestMailer->create('utf8_body', $this->recipient));

        $this->assertPattern("/\nFrom: =\?UTF-8\?Q\?Foo_=C3=A1=C3=AB=C3=B4_=C3=AE\?=\r\n =\?UTF-8\?Q\?=C3=BC\?= <extended@example.com>\r/", $Created->getEncoded());
        $this->assertPattern("/To: =\?UTF-8\?Q\?Foo_=C3=A1=C3=AB=C3=B4_=C3=AE\?=\r\n =\?UTF-8\?Q\?=C3=BC\?= <extended@example.com>, \r\n     \"Example Recipient\" <me/", $Created->getEncoded());
    }

    public function test_receive_decodes_base64_encoded_mail() {
        $TestMailer = new TestMailer();
        $TestMailer->receive(file_get_contents(AkConfig::getDir('fixtures').DS."raw_email"));
        $this->assertPattern("/Jamis/", $TestMailer->received_body);

    }

    public function test_receive_attachments() {
        $TestMailer = new TestMailer();
        $Mail = $TestMailer->receive(file_get_contents(AkConfig::getDir('fixtures').DS."raw_email2"));
        $Attachment = Ak::last($Mail->attachments);
        $this->assertEqual("smime.p7s", $Attachment->original_filename);
        $this->assertEqual("application/pkcs7-signature", $Attachment->content_type);
    }

    public function test_decode_attachment_without_charset() {
        $TestMailer = new TestMailer();
        $Mail = $TestMailer->receive(file_get_contents(AkConfig::getDir('fixtures').DS."raw_email3"));
        $Attachment = Ak::last($Mail->attachments);
        $this->assertEqual(1026, Ak::size($Attachment->data));
    }


    public function test_attachment_using_content_location() {
        $TestMailer = new TestMailer();
        $Mail = $TestMailer->receive(file_get_contents(AkConfig::getDir('fixtures').DS."raw_email12"));

        $this->assertEqual(1, Ak::size($Mail->attachments));

        $Attachment = Ak::first($Mail->attachments);
        $this->assertEqual("Photo25.jpg", $Attachment->original_filename);
    }


    public function test_attachment_with_text_type() {
        $TestMailer = new TestMailer();
        $Mail = $TestMailer->receive(file_get_contents(AkConfig::getDir('fixtures').DS."raw_email13"));

        $this->assertTrue($Mail->hasAttachments());
        $this->assertEqual(1, Ak::size($Mail->attachments));

        $Attachment = Ak::first($Mail->attachments);
        $this->assertEqual("hello.rb", $Attachment->original_filename);
    }



    public function test_decode_part_without_content_type() {
        $TestMailer = new TestMailer();
        $Mail = $TestMailer->receive(file_get_contents(AkConfig::getDir('fixtures').DS."raw_email4"));
    }

    public function test_decode_message_without_content_type() {
        $TestMailer = new TestMailer();
        $Mail = $TestMailer->receive(file_get_contents(AkConfig::getDir('fixtures').DS."raw_email5"));
    }

    public function test_decode_message_with_incorrect_charset() {
        $TestMailer = new TestMailer();
        $Mail = $TestMailer->receive(file_get_contents(AkConfig::getDir('fixtures').DS."raw_email6"));
    }


    public function test_multipart_with_mime_version() {
        $TestMailer = new TestMailer();
        $this->assertTrue($Created = $TestMailer->create('multipart_with_mime_version', $this->recipient));
        $this->assertEqual('1.1', $Created->mime_version);
    }

    public function test_multipart_with_utf8_subject() {
        $TestMailer = new TestMailer();
        $this->assertTrue($Created = $TestMailer->create('multipart_with_utf8_subject', $this->recipient));
        $this->assertPattern("/\nSubject: =\?UTF-8\?Q\?Foo_.*?\?=/", $Created->getEncoded());
    }
    public function test_multipart_with_long_russian_utf8_subject() {
        $TestMailer = new TestMailer();
        $this->assertTrue($Created = $TestMailer->create('multipart_with_long_russian_utf8_subject', $this->recipient));
        $this->assertPattern("/\nSubject: =\?UTF-8\?Q\?=D0=AD=D1=82=D0=BE_=D0=BA=D0=B0=D0=BA=D0=BE=D0=B5\?=\r\n =\?UTF-8\?Q\?-=D1=82=D0=BE_=D0=BE=D1=81=D0=BC=D1=8B=D1=81=D0=BB\?=\r\n =\?UTF-8\?Q\?=D0=B5=D0=BD=D0=BD=D0=BE=D0=B5_=D0=BD=D0=B0=D0=B4=D0=B5\?=\r\n =\?UTF-8\?Q\?=D1=8E=D1=81=D1=8C,_=D0=B4=D0=BB=D0=B8=D0=BD=D0=BD\?=\r\n =\?UTF-8\?Q\?=D1=8B=D0=B9_=D1=80=D1=83=D1=81=D1=81=D0=BA=D0=B8=D0=B9\?=\r\n =\?UTF-8\?Q\?_=D1=82=D0=B5=D0=BA=D1=81=D1=82_=D1=81_=D0=BD\?=\r\n =\?UTF-8\?Q\?=D0=B5=D0=BA=D0=BE=D1=82=D0=BE=D1=80=D1=8B=D0=BC=D0=B8_\?=\r\n =\?UTF-8\?Q\?Nice_kyril\?=\r\n =\?UTF-8\?Q\?lic_=D1=81=D0=B8=D0=BC=D0=B2=D0=BE=D0=BB\?=\r\n =\?UTF-8\?Q\?=D1=8B_=D0=B2_=D0=BD=D0=B5=D0=BC,_=D0=B8\?=\r\n =\?UTF-8\?Q\?_=D1=8F_=D0=BC=D0=BE=D0=B3=D1=83_=D0=B8=D1=81\?=\r\n =\?UTF-8\?Q\?=D0=BF=D0=BE=D0=BB=D1=8C=D0=B7=D0=BE=D0=B2=D0=B0=D1=82=D1=8C\?=\r\n =\?UTF-8\?Q\?_=D0=B5=D0=B3=D0=BE_=D0=B4=D0=BB=D1=8F_=D0=BC\?=\r\n =\?UTF-8\?Q\?=D0=BE=D0=B8=D1=85_=D1=86=D0=B5=D0=BB=D0=B5=D0=B9_\?=\r\n =\?UTF-8\?Q\?=D1=82=D0=B5=D1=81=D1=82=D0=B8=D1=80=D0=BE=D0=B2=D0=B0=D0=BD\?=\r\n =\?UTF-8\?Q\?=D0=B8=D1=8F\?=/", $Created->getEncoded());
    }

    public function test_multipart_with_long_russian_utf8_sender() {
        $TestMailer = new TestMailer();
        $this->assertTrue($Created = $TestMailer->create('multipart_with_long_russian_utf8_sender', $this->recipient));
        $this->assertPattern("/\From: =\?UTF-8\?Q\?=D0=BA=D0=B0=D0=BA=D0=BE=D0=B5\?= <test@example.com>/", $Created->getEncoded());
    }
    public function test_implicitly_multipart_with_utf8() {
        $TestMailer = new TestMailer();
        $this->assertTrue($Created = $TestMailer->create('implicitly_multipart_with_utf8', $this->recipient));
        $this->assertPattern("/\nSubject: =\?UTF-8\?Q\?Foo_.*?\?=/", $Created->getEncoded());
    }

    public function test_explicitly_multipart_with_content_type() {
        $TestMailer = new TestMailer();
        $this->assertTrue($Mail = $TestMailer->create('explicitly_multipart_example', $this->recipient));

        $this->assertEqual(3, Ak::size($Mail->parts));
        $this->assertTrue(empty($Mail->content_type));
        $this->assertEqual("multipart/alternative", $Mail->getContentType());
        $this->assertEqual("text/html", $Mail->parts[1]->content_type);
        $this->assertEqual("iso-8859-1", $Mail->parts[1]->content_type_attributes['charset']);
        $this->assertEqual("inline", $Mail->parts[1]->content_disposition);

        $this->assertEqual("image/jpeg", $Mail->parts[2]->content_type);
        $this->assertEqual("attachment", $Mail->parts[2]->content_disposition);
        $this->assertEqual("foo.jpg", $Mail->parts[2]->content_disposition_attributes['filename']);
        $this->assertEqual("foo.jpg", $Mail->parts[2]->content_type_attributes['name']);
        $this->assertTrue(empty($Mail->parts[2]->content_type_attributes['charset']));

    }

    public function test_explicitly_multipart_with_invalid_content_type() {
        $TestMailer = new TestMailer();
        $this->assertTrue($Mail = $TestMailer->create('explicitly_multipart_example', $this->recipient, 'text/xml'));

        $this->assertEqual(3, Ak::size($Mail->parts));
        $this->assertEqual("multipart/alternative", $Mail->getContentType());

    }


    public function test_implicitly_multipart_messages() {
        $TestMailer = new TestMailer();
        $this->assertTrue($Mail = $TestMailer->create('implicitly_multipart_example', $this->recipient));

        $this->assertEqual(3, Ak::size($Mail->parts));
        $this->assertEqual("1.0", $Mail->mime_version);
        $this->assertEqual("multipart/alternative", $Mail->content_type);

        $this->assertEqual("text/yaml", $Mail->parts[0]->content_type);
        $this->assertEqual('UTF-8', $Mail->parts[0]->content_type_attributes['charset']);

        $this->assertEqual("text/plain", $Mail->parts[1]->content_type);
        $this->assertEqual('UTF-8', $Mail->parts[1]->content_type_attributes['charset']);

        $this->assertEqual("text/html", $Mail->parts[2]->content_type);
        $this->assertEqual('UTF-8', $Mail->parts[2]->content_type_attributes['charset']);

    }

    public function test_implicitly_multipart_messages_with_custom_order() {
        $TestMailer = new TestMailer();
        $this->assertTrue($Mail = $TestMailer->create('implicitly_multipart_example', $this->recipient, null, array("text/yaml", "text/plain")));

        $this->assertEqual(3, Ak::size($Mail->parts));
        $this->assertEqual("text/html", $Mail->parts[0]->content_type);
        $this->assertEqual("text/plain", $Mail->parts[1]->content_type);
        $this->assertEqual("text/yaml", $Mail->parts[2]->content_type);
    }

    public function test_implicitly_multipart_messages_with_charset() {
        $TestMailer = new TestMailer();
        $this->assertTrue($Mail = $TestMailer->create('implicitly_multipart_example', $this->recipient, 'iso-8859-1'));
        $this->assertEqual("multipart/alternative", $Mail->content_type);

        $this->assertEqual('iso-8859-1', $Mail->parts[0]->content_type_attributes['charset']);
        $this->assertEqual('iso-8859-1', $Mail->parts[1]->content_type_attributes['charset']);
        $this->assertEqual('iso-8859-1', $Mail->parts[2]->content_type_attributes['charset']);
    }

    public function test_html_mail() {
        $TestMailer = new TestMailer();
        $this->assertTrue($Mail = $TestMailer->create('html_mail', $this->recipient));
        $this->assertEqual("text/html", $Mail->content_type);
    }

    public function test_html_mail_with_underscores() {
        $TestMailer = new TestMailer();
        $this->assertTrue($Mail = $TestMailer->create('html_mail_with_underscores', $this->recipient));
        $this->assertEqual('<a href="http://google.com" target="_blank">_Google</a>', $Mail->body);
    }

    public function test_various_newlines() {
        $TestMailer = new TestMailer();
        $this->assertTrue($Mail = $TestMailer->create('various_newlines', $this->recipient));
        $this->assertEqual("line #1\nline #2\nline #3\nline #4\n\n".
        "line #5\n\nline#6\n\nline #7", $Mail->body);
    }

    public function test_various_newlines_multipart() {
        $TestMailer = new TestMailer();
        $this->assertTrue($Mail = $TestMailer->create('various_newlines_multipart', $this->recipient));
        $this->assertEqual("line #1\nline #2\nline #3\nline #4\n\n", $Mail->parts[0]->body);
        $this->assertEqual("<p>line #1</p>\n<p>line #2</p>\n<p>line #3</p>\n<p>line #4</p>\n\n", $Mail->parts[1]->body);
    }

    public function test_headers_removed_on_smtp_delivery() {
        $TestMailer = new TestMailer();
        $this->assertTrue($Mail = $TestMailer->create('various_newlines_multipart', $this->recipient));
        $this->assertEqual("line #1\nline #2\nline #3\nline #4\n\n", $Mail->parts[0]->body);
        $this->assertEqual("<p>line #1</p>\n<p>line #2</p>\n<p>line #3</p>\n<p>line #4</p>\n\n", $Mail->parts[1]->body);
    }


    public function test_recursive_multipart_processing() {
        $TestMailer = new TestMailer();
        $Mail = $TestMailer->receive(file_get_contents(AkConfig::getDir('fixtures').DS."raw_email7"));
        $this->assertEqual("This is the first part.\n\nAttachment: test.rb\nAttachment: test.pdf\n\n\nAttachment: smime.p7s\n", $Mail->bodyToString());
    }

    public function test_decode_encoded_attachment_filename() {
        $TestMailer = new TestMailer();
        $Mail = $TestMailer->receive(file_get_contents(AkConfig::getDir('fixtures').DS."raw_email8"));
        $Attachment = Ak::last($Mail->attachments);
        $this->assertEqual("01QuienTeDijat.Pitbull.mp3", $Attachment->original_filename);
    }

    public function test_wrong_mail_header() {
        $TestMailer = new TestMailer();
        $Mail = $TestMailer->receive(file_get_contents(AkConfig::getDir('fixtures').DS."raw_email9"));
        $this->assertTrue(empty($Mail->quite));
    }

    public function test_decode_message_with_unquoted_atchar_in_header() {
        $TestMailer = new TestMailer();
        $Mail = $TestMailer->receive(file_get_contents(AkConfig::getDir('fixtures').DS."raw_email11"));
        $this->assertTrue(!empty($Mail->from));
    }

    public function test_should_encode_alternative_message_from_templates() {
        $TestMailer = new TestMailer();
        $Message = $TestMailer->create('alternative_message_from_templates', $this->recipient);
        $rendered_message = $TestMailer->getRawMessage();


        $this->assertPattern(   '/Content-Type: multipart\/alternative;charset=UTF-8;boundary=[a-f0-9]{32}\r\n'.
        'Mime-Version: 1.0\r\n'.
        'Subject:/', $rendered_message);
        $this->assertPattern('/To:/', $rendered_message);
        $this->assertPattern('/Date:/', $rendered_message);
        $this->assertPattern('/--[a-f0-9]{32}\r\nContent-Type: text\/plain;charset=UTF-8\r\nContent-Transfer-Encoding: quoted-printable\r\nContent-Disposition: inline/', $rendered_message);
        $this->assertPattern('/--[a-f0-9]{32}\r\nContent-Type: text\/html;charset=UTF-8\r\nContent-Transfer-Encoding: quoted-printable\r\nContent-Disposition: inline/', $rendered_message);
        $this->assertPattern('/--[a-f0-9]{32}--/', $rendered_message);
    }

    public function test_should_deliver_creating_message() {
        $TestMailer = new TestMailer();
        $Message = $TestMailer->deliver('alternative_message_from_templates', $this->recipient);
        $this->assertPattern('/Subject: Alternative message from template/', $TestMailer->deliveries[0]);
    }

    public function test_should_allow_using_text_helper_on_mail_views() {
        $TestMailer = new TestMailer();
        $Message = $TestMailer->create('message_with_helpers', $this->recipient);
        $rendered_message = $TestMailer->getRawMessage();
        $this->assertPattern('/<a href="http:\/\/example.com\/offers">Our offers<\/a>/', $rendered_message);
        $this->assertNoPattern('/text_helper/', $rendered_message);
    }

    public function test_should_add_from_name() {
        $TestMailer = new TestMailer();
        $Message = $TestMailer->create('message_from_first_name', array('No One'=>'no.one@example.com'));
        $rendered_message = $TestMailer->getRawMessage();
       $this->assertPattern('/To: "No One" <no\.one@example\.com>/', $rendered_message);
       $this->assertPattern('/From: "Some \\\"One" <some\.one@example\.com>/', $rendered_message);
    }


    public function test_should_encode_alternative_message_from_templates_with_embeded_images() {
        $TestMailer = new TestMailer();
        $Message = $TestMailer->create('alternative_message_from_templates', $this->recipient, true);

        $rendered_message = $TestMailer->getRawMessage();

        $this->assertPattern('/==\r\n--[a-f0-9]{32}--\r\n\r\n--[a-f0-9]{32}--\r\n$/', $rendered_message, 'Closing 2 boundaries');
        $this->assertPattern('/([A-Za-z0-9\/+]{76}\r\n){30,}/', $rendered_message, 'large base64 encoded file');


        $this->assertPattern(
        '/<\/html>\r\n\r\n--[a-f0-9]{32}\r\n'.
        'Content-Type: image\/png;name=([^\.]{20,})\.png\r\n'.
        'Content-Transfer-Encoding: base64\r\n'.
        'Content-Id: <\\1\.png>\r\n'.
        'Content-Disposition: inline;filename=\\1\.png\r\n'.
        '\r\n[A-Za-z0-9\/+]{76}/', $rendered_message, 'inline image headers');

        $this->assertPattern('/<img src=3D"cid:([^\.]{20,})\.png" \/>/', $rendered_message, 'Image src pointing to cid');


        $this->assertPattern('/--([a-f0-9]{32})\r\n'.
        'Content-Type: text\/plain;charset=UTF-8\r\n'.
        'Content-Transfer-Encoding: quoted-printable\r\n'.
        'Content-Disposition: inline\r\n\r\n'.
        'Rendered as Text\r\n\r\n'.
        '--\\1\r\n'.
        'Content-Type: multipart\/related;charset=UTF-8;boundary=([a-f0-9]{32})\r\n\r\n\r\n\r\n'.
        '--\\2\r\n'.
        'Content-Type: text\/html;charset=UTF-8\r\n'.
        'Content-Transfer-Encoding: quoted-printable\r\n'.
        'Content-Disposition: inline\r\n\r\n'.
        '<html>/', $rendered_message, 'Multipart nesting');

        $this->assertPattern('/Content-Type: multipart\/alternative;charset=UTF-8;boundary=[a-f0-9]{32}\r\nMime-Version: 1.0/', $rendered_message, 'main headers');

    }

    public function test_should_encode_alternative_message_from_templates_with_external_embeded_images() {
        if(!@file_get_contents('http://www.bermilabs.com/images/bermilabs_logo.png')) return; // offline mode
        $TestMailer = new TestMailer();
        $Message = $TestMailer->create('alternative_message_from_templates', $this->recipient, true, true);
        //$TestMailer->delivery_method = 'php';
        //$TestMailer->deliver($Message);
        $rendered_message = $TestMailer->getRawMessage();
        $this->assertPattern('/==\r\n\r\n--[a-f0-9]{32}\r\nContent-Type: image\/png;/', $rendered_message, 'Two images embeded');
    }
}

ak_test_case('ActionMailer_TestCase');

