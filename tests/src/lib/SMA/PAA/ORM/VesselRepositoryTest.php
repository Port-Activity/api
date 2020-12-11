<?php
namespace SMA\PAA\ORM;

use PHPUnit\Framework\TestCase;

//TODO: fix this
final class VesselRepositoryTest extends TestCase
{
    public function testSavingObjectGeneratesCorrectSqlAndValues(): void
    {
        $db = new FakeConnection();
        $repository = new VesselRepository();
        $repository->setDb($db);
        $model = new VesselModel();
        $model->id = 123;
        $model->imo = 1234567;
        $model->vessel_name = "Vessel Name";
        $model->vessel_type = 6;
        $repository->save($model, true);
        $last = $db->lastQuery();
        $this->assertEquals(
            '"UPDATE public.vessel '
            . 'SET created_at = ?,created_by = ?,modified_at = ?,modified_by = ?,imo = ?,vessel_name = ?,'
            . 'visible = ?,vessel_type = ? WHERE id=?"',
            json_encode(array_shift($last))
        );
        $last[0] = "<date>";
        $last[2] = "<date>";
        $this->assertEquals(
            '["<date>",null,"<date>",0,1234567,"Vessel Name","t",6,123]',
            json_encode($last)
        );
    }
    public function testSavingNewObjectWithoutExistingVesselNameGeneratesCorrectSqlAndValues(): void
    {
        $db = new FakeConnection([]);
        $repository = new VesselRepository();
        $repository->setDb($db);
        $model = new VesselModel();
        $model->imo = 1234567;
        $model->vessel_name = "Vessel Name";
        $model->vessel_type = 8;
        $repository->save($model, true);
        $last = $db->lastQuery();
        $this->assertEquals(
            '"INSERT INTO public.vessel (created_at,created_by,modified_at,modified_by,imo,vessel_name,'
            .'visible,vessel_type) VALUES (?,?,?,?,?,?,?,?)"',
            json_encode(array_shift($last))
        );
        $last[0] = "<date>";
        $last[2] = "<date>";
        $this->assertEquals(
            '["<date>",0,"<date>",0,1234567,"Vessel Name","t",8]',
            json_encode($last)
        );
    }
    /*
    public function testSavingNewObjectWithExistingVesselNameGeneratesCorrectSqlAndValues(): void
    {
        $db = new FakeConnection();
        $repository = new VesselRepository();
        $repository->setDb($db);
        $model = new VesselModel();
        $model->imo = 1234567;
        $model->vessel_name = "Vessel Name";
        $repository->save($model);
        $last = $db->lastQuery();
        $this->assertEquals(
            '"UPDATE public.vessel SET created_at = ?,'
            . 'created_by = ?,modified_at = ?,modified_by = ?'
            . ',imo = ?,vessel_name = ?'
            . ' WHERE id=?"',
            json_encode(array_shift($last))
        );
        $last[0] = "<date>";
        $last[2] = "<date>";
        $this->assertEquals(
            '',
            json_encode($last)
        );

    }
    */
    public function testDeletingObjectGeneratesCorrectSqlAndValues(): void
    {
        $db = new FakeConnection();
        $repository = new VesselRepository();
        $repository->setDb($db);
        $model = new VesselModel();
        $model->id = 234;
        $model->imo = 1234567;
        $model->vessel_name = "Vessel Name";
        $repository->delete([$model->id], true);
        $last = $db->lastQuery();
        $this->assertEquals(
            '"DELETE FROM public.vessel WHERE id IN (?)"',
            json_encode(array_shift($last))
        );
        $this->assertEquals(
            '[234]',
            json_encode($last)
        );
    }
    public function testSaveValidImoExcplicitId(): void
    {
        $db = new FakeConnection();
        $repository = new VesselRepository();
        $repository->setDb($db);
        $model = new VesselModel();
        $model->id = 123;
        $model->imo = 1234567;
        $model->vessel_name = "Vessel Name";
        $model->vessel_type = 8;
        $repository->saveValidImo($model, true);
        $last = $db->lastQuery();
        $this->assertEquals(
            '"UPDATE public.vessel '
            . 'SET created_at = ?,created_by = ?,modified_at = ?,modified_by = ?,imo = ?,vessel_name = ?,'
            . 'visible = ?,vessel_type = ? WHERE id=?"',
            json_encode(array_shift($last))
        );
        $last[0] = "<date>";
        $last[2] = "<date>";
        $this->assertEquals(
            '["<date>",null,"<date>",0,1234567,"Vessel Name","t",8,123]',
            json_encode($last)
        );
    }
    /*
    public function testSaveValidImoNoIdExistingImo(): void
    {
        $db = new FakeConnection();
        $repository = new VesselRepository();
        $repository->setDb($db);
        $model = new VesselModel();
        $model->imo = 1234567;
        $model->vessel_name = "Vessel Name";
        $repository->saveValidImo($model);
        $last = $db->lastQuery();
        $this->assertEquals(
            '["UPDATE public.vessel SET created_at = ?,'
            . 'created_by = ?,modified_at = ?,modified_by = ?'
            . ',imo = ?,vessel_name = ?'
            . ' WHERE id=?",null,null,null,null,1234567,"Vessel Name",111]',
            json_encode(array_shift($last))
        );
        $last[0] = "<date>";
        $last[2] = "<date>";
        $this->assertEquals(
            '["UPDATE public.vessel SET created_at = ?,'
            . 'created_by = ?,modified_at = ?,modified_by = ?'
            . ',imo = ?,vessel_name = ?'
            . ' WHERE id=?",null,null,null,null,7654321,"Vessel Name",111]',
            json_encode($last)
        );

    }
    */
    public function testSaveValidImoNoIdNonExistingImo(): void
    {
        $db = new FakeConnection([]);
        $repository = new VesselRepository();
        $repository->setDb($db);
        $model = new VesselModel();
        $model->imo = 1234567;
        $model->vessel_name = "Vessel Name";
        $repository->saveValidImo($model, true);
        $last = $db->lastQuery();
        $this->assertEquals(
            '"INSERT INTO public.vessel (created_at,created_by,modified_at,modified_by,imo,vessel_name,visible,'
            . 'vessel_type) VALUES (?,?,?,?,?,?,?,?)"',
            json_encode(array_shift($last))
        );
        $last[0] = "<date>";
        $last[2] = "<date>";
        $this->assertEquals(
            '["<date>",0,"<date>",0,1234567,"Vessel Name","t",null]',
            json_encode($last)
        );
    }
    public function testSaveFakeImoExistingVesselName(): void
    {
        $db = new FakeConnection([["id" => 111, "imo" => 7654321, "vessel_name" => "Vessel Name"]]);
        $repository = new VesselRepository();
        $repository->setDb($db);
        $model = new VesselModel();
        $model->imo = 0;
        $model->vessel_name = "Vessel Name";
        $model->vessel_type = 9;
        $repository->saveFakeImo($model, true);
        $last = $db->lastQuery();
        $this->assertEquals(
            '"UPDATE public.vessel '
            . 'SET created_at = ?,created_by = ?,modified_at = ?,modified_by = ?,imo = ?,vessel_name = ?,visible = ?,'
            . 'vessel_type = ? WHERE id=?"',
            json_encode(array_shift($last))
        );
        $last[0] = "<date>";
        $last[2] = "<date>";
        $this->assertEquals(
            '["<date>",0,"<date>",0,7654321,"Vessel Name","t",9,111]',
            json_encode($last)
        );
    }
    /*
    public function testSaveFakeImoNonExistingVesselName(): void
    {
        $db = new FakeConnection([]);
        $repository = new VesselRepository();
        $repository->setDb($db);
        $model = new VesselModel();
        $model->imo = 0;
        $model->vessel_name = "VesselRepositoryTest::testSaveFakeImoNonExistingVesselName";
        $repository->saveFakeImo($model);
        $last = $db->lastQuery();
        $this->assertEquals(
            '"UPDATE public.vessel SET created_at = ?,'
            . 'created_by = ?,modified_at = ?,modified_by = ?'
            . ',imo = ?,vessel_name = ?'
            . ' WHERE id=?"',
            json_encode(array_shift($last))
        );
    }
    */
    public function testGetImoSqlQuery(): void
    {
        $db = new FakeConnection([["id" => 111, "imo" => 7654321]]);
        $repository = new VesselRepository();
        $repository->setDb($db);
        $vesselName = "Vessel Name";
        $repository->getImo($vesselName);
        $last = $db->lastQuery();
        $this->assertEquals(
            '["SELECT id,created_at,created_by,modified_at,modified_by,imo,vessel_name,visible,vessel_type ' .
            'FROM public.vessel WHERE LOWER(vessel_name)=LOWER(?) ORDER BY id LIMIT ? OFFSET ?","Vessel Name",1,0]',
            json_encode($last)
        );
    }
    public function testGetImoReturnValue(): void
    {
        $db = new FakeConnection([["id" => 111, "imo" => 7654321]]);
        $repository = new VesselRepository();
        $repository->setDb($db);
        $vesselName = "Vessel Name";
        $res = $repository->getImo($vesselName);
        $this->assertEquals(7654321, $res);
    }
}
